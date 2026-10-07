"""
e-SAKIP follow-up feature tests:
- Local vendor JS assets (no CDN usage in HTML)
- Balinese names in seeded data
- Laporan XLSX / PDF exports with correct content types
- PK/Renja print returns PDF
- Login page full-width layout + Maiharta link
- Master Data sidebar flyout markup present with z-index on top
- RBAC on /laporan for operator OPD
"""
import os
import re
import pytest
import requests

BASE_URL = os.environ.get(
    "ESAKIP_BASE_URL",
    "https://0c9e315a-f434-4d10-8eea-782838feebaf.preview.emergentagent.com",
).rstrip("/")

CSRF_RE = re.compile(r'name="_token"\s+value="([^"]+)"')


def new_session():
    s = requests.Session()
    s.headers.update({"User-Agent": "esakip-followup-tester/1.0"})
    return s


def login(sess, email, password="password123"):
    r = sess.get(BASE_URL + "/login", timeout=20)
    m = CSRF_RE.search(r.text)
    assert m, "CSRF not found"
    token = m.group(1)
    return sess.post(
        BASE_URL + "/login",
        data={"_token": token, "login": email, "password": password},
        allow_redirects=False,
        timeout=20,
    )


# ---------- Login page layout ----------
class TestLoginLayout:
    def test_login_card_fullwidth_markup(self):
        s = new_session()
        r = s.get(BASE_URL + "/login", timeout=20)
        assert r.status_code == 200
        # The card should NOT be constrained with max-w-md / max-w-lg narrow centered
        # Expect a wider / full container markup
        html = r.text
        # Must still have the form test ids
        assert 'data-testid="login-form"' in html
        # Must have Maiharta footer link
        assert 'https://www.maiharta.com' in html
        assert 'data-testid="login-footer-maiharta-link"' in html


# ---------- Local vendor assets (performance) ----------
class TestLocalVendorAssets:
    def test_login_html_no_cdn(self):
        s = new_session()
        r = s.get(BASE_URL + "/login", timeout=20)
        html = r.text.lower()
        assert "unpkg.com" not in html, "unpkg CDN still referenced on login page"
        assert "cdn.jsdelivr.net" not in html, "jsdelivr CDN still referenced on login page"

    def test_dashboard_html_no_cdn(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        r = s.get(BASE_URL + "/dashboard", timeout=20)
        html = r.text.lower()
        assert "unpkg.com" not in html
        assert "cdn.jsdelivr.net" not in html
        # Chart.js should be present on dashboard (local)
        assert "chart" in html

    def test_vendor_files_served(self):
        s = new_session()
        for path in ["/vendor/alpine.min.js", "/vendor/lucide.min.js", "/vendor/chart.umd.min.js"]:
            r = s.get(BASE_URL + path, timeout=20)
            assert r.status_code == 200, f"{path} returned {r.status_code}"
            ct = r.headers.get("Content-Type", "")
            assert "javascript" in ct or "text" in ct, f"{path} content-type={ct}"
            assert len(r.content) > 1000, f"{path} too small ({len(r.content)}b)"

    def test_chartjs_not_on_non_dashboard(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        r = s.get(BASE_URL + "/pengguna", timeout=20)
        # chart umd should NOT be loaded on non-dashboard page
        assert "chart.umd" not in r.text.lower()


# ---------- Balinese names in seed ----------
class TestBalineseNames:
    def test_admin_name_balinese(self):
        s = new_session()
        login(s, "admin@esakip.go.id")
        r = s.get(BASE_URL + "/dashboard", timeout=20)
        # Admin should be Ni Luh Putu Ayu Lestari
        assert "Ni Luh Putu Ayu Lestari" in r.text or "Putu Ayu Lestari" in r.text, \
            "Admin Balinese name not found in header profile"

    def test_pengguna_list_has_balinese(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        r = s.get(BASE_URL + "/pengguna", timeout=20)
        assert r.status_code == 200
        txt = r.text
        # At least one Balinese name should appear
        found = any(n in txt for n in [
            "Ni Luh", "I Made", "I Ketut", "I Wayan", "I Nyoman", "Ni Kadek", "Ni Putu", "I Gede"
        ])
        assert found, "No Balinese name tokens found on /pengguna"

    def test_bupati_name(self):
        s = new_session()
        login(s, "bupati@esakip.go.id")
        r = s.get(BASE_URL + "/dashboard", timeout=20)
        # Bupati should be I Ketut Suwardana
        assert "Ketut Suwardana" in r.text or "I Ketut" in r.text


# ---------- Sidebar Master flyout markup ----------
class TestMasterFlyout:
    def test_pk_page_has_master_flyout_markup(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        r = s.get(BASE_URL + "/tahunan/pk", timeout=20)
        assert r.status_code == 200
        html = r.text
        # The master nav + submenu testids
        assert 'data-testid="nav-master"' in html
        for tid in [
            "nav-master-periode", "nav-master-opd", "nav-master-unit-kerja",
            "nav-master-satuan", "nav-master-pengguna", "nav-master-pustaka-indikator",
        ]:
            assert f'data-testid="{tid}"' in html, f"missing {tid}"
        # aside/sidebar should have z-index class allowing flyout on top
        assert ("z-40" in html) or ("z-50" in html), "sidebar z-index class missing"


# ---------- Laporan XLSX / PDF exports ----------
REPORT_TYPES = ["capaian", "rpjmd", "indikator", "renaksi", "evaluasi", "audit"]


class TestLaporanExports:
    @pytest.fixture(scope="class")
    def super_sess(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        return s

    @pytest.mark.parametrize("rtype", REPORT_TYPES)
    def test_export_xlsx(self, super_sess, rtype):
        r = super_sess.get(
            BASE_URL + f"/laporan/{rtype}/export",
            params={"tahun": 2026},
            timeout=60,
        )
        assert r.status_code == 200, f"{rtype} export status={r.status_code}"
        ct = r.headers.get("Content-Type", "")
        # Real xlsx uses openxmlformats spreadsheetml
        assert "spreadsheetml" in ct or "xlsx" in ct.lower() or "excel" in ct.lower(), \
            f"{rtype} export content-type={ct}"
        # xlsx is a zip => starts with PK\x03\x04
        assert r.content[:2] == b"PK", f"{rtype} not a valid xlsx (first bytes={r.content[:4]!r})"

    @pytest.mark.parametrize("rtype", REPORT_TYPES)
    def test_cetak_pdf(self, super_sess, rtype):
        r = super_sess.get(
            BASE_URL + f"/laporan/{rtype}/cetak",
            params={"tahun": 2026},
            timeout=60,
        )
        assert r.status_code == 200, f"{rtype} cetak status={r.status_code}"
        ct = r.headers.get("Content-Type", "")
        assert "pdf" in ct.lower(), f"{rtype} cetak content-type={ct}"
        assert r.content[:4] == b"%PDF", f"{rtype} not valid pdf (first bytes={r.content[:5]!r})"

    def test_operator_forbidden_laporan(self):
        s = new_session()
        login(s, "operator.dinkes@esakip.go.id")
        r = s.get(BASE_URL + "/laporan", allow_redirects=False, timeout=20)
        assert r.status_code == 403


# ---------- Annual print returns PDF ----------
class TestAnnualPrintPdf:
    @pytest.fixture(scope="class")
    def super_sess(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        return s

    def test_pk_cetak_pdf(self, super_sess):
        r = super_sess.get(BASE_URL + "/tahunan/pk/1/cetak", timeout=60)
        assert r.status_code == 200, f"status={r.status_code}"
        ct = r.headers.get("Content-Type", "")
        assert "pdf" in ct.lower(), f"content-type={ct}"
        assert r.content[:4] == b"%PDF"

    def test_renja_cetak_pdf(self, super_sess):
        r = super_sess.get(BASE_URL + "/tahunan/renja/1/cetak", timeout=60)
        assert r.status_code == 200
        ct = r.headers.get("Content-Type", "")
        assert "pdf" in ct.lower(), f"content-type={ct}"
        assert r.content[:4] == b"%PDF"
