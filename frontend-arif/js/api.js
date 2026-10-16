const API = "../backend/index.php";
let token = localStorage.getItem("event_token") || "",
	user = null,
	register = false,
	page = 1;
const el = (id) => document.getElementById(id);
const html = (s) =>
	String(s ?? "").replace(
		/[&<>"']/g,
		(c) =>
			({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
				c
			],
	);
function notice(msg, bad = false) {
	el("alert").innerHTML =
		`<div class="alert ${bad ? "alert-danger" : "alert-success"}">${html(msg)}</div>`;
	window.scrollTo(0, 0);
}
async function request(path, method = "GET", data) {
	let headers = { Accept: "application/json" };
	if (token) headers.Authorization = "Bearer " + token;
	if (data !== undefined) headers["Content-Type"] = "application/json";
	let resp = await fetch(API + path, {
		method,
		headers,
		body: data === undefined ? undefined : JSON.stringify(data),
	});
	let val = await resp
		.json()
		.catch(() => ({ error: "Invalid server response" }));
	if (!resp.ok) throw Error(val.error || `HTTP ${resp.status}`);
	return val;
}
