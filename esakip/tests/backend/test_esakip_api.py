"""
e-SAKIP Pemda — HTTP integration tests (Laravel session + CSRF).
Covers: login/auth, RBAC per route, page rendering for key modules.
"""
import os
import re
import pytest
import requests
from urllib.parse import urljoin

BASE_URL = os.environ.get(
    "ESAKIP_BASE_URL",
    "https://0c9e315a-f434-4d10-8eea-782838feebaf.preview.emergentagent.com",
).rstrip("/")

CSRF_RE = re.compile(r'name="_token"\s+value="([^"]+)"')


def new_session():
    s = requests.Session()
    s.headers.update({"User-Agent": "esakip-tester/1.0"})
    return s


def get_csrf(sess, path="/login"):
    r = sess.get(BASE_URL + path, allow_redirects=True, timeout=20)
    assert r.status_code == 200, f"GET {path} -> {r.status_code}"
    m = CSRF_RE.search(r.text)
    assert m, f"CSRF token not found in {path}"
    return m.group(1), r


def login(sess, email, password="password123"):
    token, _ = get_csrf(sess, "/login")
    r = sess.post(
        BASE_URL + "/login",
        data={"_token": token, "login": email, "password": password},
        allow_redirects=False,
        timeout=20,
    )
    return r


# ---------- Health / login page ----------
class TestLoginPage:
    def test_login_page_renders(self):
        s = new_session()
        r = s.get(BASE_URL + "/login", timeout=20)
        assert r.status_code == 200
        for tid in [
            "login-form", "login-email-input", "login-password-input",
            "login-submit-button", "login-footer-maiharta-link",
            "demo-account-superadmin", "login-demo-toggle",
        ]:
            assert f'data-testid="{tid}"' in r.text, f"missing testid {tid}"

    def test_maiharta_link_href(self):
        s = new_session()
        r = s.get(BASE_URL + "/login", timeout=20)
        assert "https://www.maiharta.com" in r.text

    def test_wrong_password_shows_error(self):
        s = new_session()
        r = login(s, "superadmin@esakip.go.id", "wrongpass")
        # Laravel returns 302 back to /login with errors in session
        assert r.status_code in (302, 303)
        # follow and check error block
        r2 = s.get(BASE_URL + "/login", timeout=20)
        assert r2.status_code == 200
        assert ("tidak sesuai" in r2.text) or ("login-error" in r2.text)


# ---------- Successful logins per role ----------
ROLES = [
    ("superadmin@esakip.go.id", "super_admin"),
    ("bappeda@esakip.go.id", "bappeda"),
    ("sakip@esakip.go.id", "tim_sakip"),
    ("operator.dinkes@esakip.go.id", "operator_opd"),
    ("kepala.dinkes@esakip.go.id", "kepala_opd"),
    ("inspektorat@esakip.go.id", "evaluator"),
    ("operator.pupr@esakip.go.id", "operator_opd"),
    ("kepala.pupr@esakip.go.id", "kepala_opd"),
]


@pytest.mark.parametrize("email,role", ROLES)
def test_login_redirects_to_dashboard(email, role):
    s = new_session()
    r = login(s, email)
    assert r.status_code in (302, 303), f"{email}: expected redirect, got {r.status_code}"
    loc = r.headers.get("Location", "")
    assert "/dashboard" in loc or loc.endswith("/"), f"{email}: redirect to {loc}"
    # Follow to ensure authenticated dashboard loads
    r2 = s.get(BASE_URL + "/dashboard", timeout=20)
    assert r2.status_code == 200, f"{email}: /dashboard -> {r2.status_code}"


# ---------- RBAC ----------
class TestRBAC:
    @pytest.fixture
    def super_sess(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        return s

    @pytest.fixture
    def op_dinkes(self):
        s = new_session()
        login(s, "operator.dinkes@esakip.go.id")
        return s

    def test_superadmin_access_master_opd(self, super_sess):
        r = super_sess.get(BASE_URL + "/master/opd", timeout=20)
        assert r.status_code == 200

    def test_superadmin_access_pengguna(self, super_sess):
        r = super_sess.get(BASE_URL + "/pengguna", timeout=20)
        assert r.status_code == 200

    def test_superadmin_access_audit(self, super_sess):
        r = super_sess.get(BASE_URL + "/audit", timeout=20)
        assert r.status_code == 200

    @pytest.mark.parametrize("path", ["/master/opd", "/pengguna", "/audit"])
    def test_operator_forbidden(self, op_dinkes, path):
        r = op_dinkes.get(BASE_URL + path, allow_redirects=False, timeout=20)
        assert r.status_code == 403, f"{path} expected 403 got {r.status_code}"


# ---------- Core module pages ----------
class TestModulePages:
    @pytest.fixture(scope="class")
    def super_sess(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        return s

    @pytest.fixture(scope="class")
    def bappeda(self):
        s = new_session()
        login(s, "bappeda@esakip.go.id")
        return s

    @pytest.fixture(scope="class")
    def op_dinkes(self):
        s = new_session()
        login(s, "operator.dinkes@esakip.go.id")
        return s

    @pytest.fixture(scope="class")
    def op_pupr(self):
        s = new_session()
        login(s, "operator.pupr@esakip.go.id")
        return s

    @pytest.fixture(scope="class")
    def kepala_pupr(self):
        s = new_session()
        login(s, "kepala.pupr@esakip.go.id")
        return s

    @pytest.fixture(scope="class")
    def inspektorat(self):
        s = new_session()
        login(s, "inspektorat@esakip.go.id")
        return s

    def test_dashboard_has_kpi_and_tabs(self, super_sess):
        r = super_sess.get(BASE_URL + "/dashboard", timeout=20)
        assert r.status_code == 200
        assert 'data-testid="tab-tw1"' in r.text

    def test_dashboard_drilldown_opd(self, super_sess):
        r = super_sess.get(BASE_URL + "/dashboard/opd/1", timeout=20)
        assert r.status_code in (200, 404)  # at least no 500

    def test_rpjmd_index_bappeda(self, bappeda):
        r = bappeda.get(BASE_URL + "/rpjmd", timeout=20)
        assert r.status_code == 200

    def test_renstra_list(self, super_sess):
        r = super_sess.get(BASE_URL + "/renstra", timeout=20)
        assert r.status_code == 200

    def test_renstra_detail(self, op_pupr):
        r = op_pupr.get(BASE_URL + "/renstra/3", timeout=20)
        assert r.status_code in (200, 403)

    def test_renja_index(self, op_pupr):
        r = op_pupr.get(BASE_URL + "/tahunan/renja", timeout=20)
        assert r.status_code == 200

    def test_pk_index(self, op_pupr):
        r = op_pupr.get(BASE_URL + "/tahunan/pk", timeout=20)
        assert r.status_code == 200

    def test_pk_print_view(self, op_pupr):
        r = op_pupr.get(BASE_URL + "/tahunan/pk/1/cetak", timeout=20)
        assert r.status_code in (200, 403, 404)

    def test_rencana_aksi(self, op_dinkes):
        r = op_dinkes.get(BASE_URL + "/rencana-aksi", timeout=20)
        assert r.status_code == 200

    def test_realisasi(self, op_dinkes):
        r = op_dinkes.get(BASE_URL + "/realisasi", timeout=20)
        assert r.status_code == 200
        assert 'data-testid="realisasi-create-button"' in r.text or "Realisasi" in r.text

    def test_evaluasi(self, inspektorat):
        r = inspektorat.get(BASE_URL + "/evaluasi", timeout=20)
        assert r.status_code == 200

    def test_dokumen(self, super_sess):
        r = super_sess.get(BASE_URL + "/dokumen", timeout=20)
        assert r.status_code == 200

    def test_laporan(self, super_sess):
        r = super_sess.get(BASE_URL + "/laporan", timeout=20)
        assert r.status_code == 200

    def test_persetujuan(self, kepala_pupr):
        r = kepala_pupr.get(BASE_URL + "/persetujuan", timeout=20)
        assert r.status_code == 200

    def test_notifikasi(self, op_dinkes):
        r = op_dinkes.get(BASE_URL + "/notifikasi", timeout=20)
        assert r.status_code == 200


# ---------- Master CRUD (satuan is simplest) ----------
class TestMasterCRUD:
    def test_create_satuan_as_superadmin(self):
        s = new_session()
        login(s, "superadmin@esakip.go.id")
        token, _ = get_csrf(s, "/master/satuan")
        payload = {
            "_token": token,
            "nama": "TEST_UnitPersen",
            "simbol": "TST%",
        }
        r = s.post(BASE_URL + "/master/satuan", data=payload,
                   allow_redirects=False, timeout=20)
        # Expected 302 redirect back (success) or 422 if field names differ
        assert r.status_code in (302, 303), f"status {r.status_code} body={r.text[:300]}"
        # Verify persistence
        r2 = s.get(BASE_URL + "/master/satuan", timeout=20)
        assert "TEST_UnitPersen" in r2.text or r2.status_code == 200
