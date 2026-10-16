function toggleRegister() {
	register = !register;
	el("name").classList.toggle("d-none", !register);
	el("name").required = register;
	el("authTitle").textContent = register
		? "Create customer account"
		: "Sign in";
	el("authSubmit").textContent = register ? "Register" : "Login";
	el("toggleText").textContent = register
		? "Already registered? Sign in"
		: "Create a customer account";
}
el("loginForm").onsubmit = async (e) => {
	e.preventDefault();
	try {
		if (register) {
			await request("/auth/register", "POST", {
				name: el("name").value,
				email: el("email").value,
				password: el("password").value,
			});
			toggleRegister();
			notice("Account created. Please sign in.");
			return;
		}
		let info = await request("/auth/login", "POST", {
			email: el("email").value,
			password: el("password").value,
		});
		if (!info.token) throw Error("Login succeeded but no token was returned by the server.");
		token = info.token;
		localStorage.setItem("event_token", token);
		await init();
	} catch (err) {
		notice(err.message, true);
	}
};
function logout() {
	localStorage.removeItem("event_token");
	token = "";
	user = null;
	el("auth").classList.remove("d-none");
	el("app").classList.add("d-none");
	el("identity").innerHTML = "";
}
async function init() {
	try {
		user = await request("/auth/me");
		el("auth").classList.add("d-none");
		el("app").classList.remove("d-none");
		el("identity").innerHTML =
			`<span>${html(user.name)} (${html(user.role)})</span><button class="btn btn-light btn-sm" onclick="logout()">Logout</button>`;
		document
			.querySelectorAll(".manager")
			.forEach((x) =>
				x.classList.toggle(
					"d-none",
					!["admin", "organiser"].includes(user.role),
				),
			);
		document
			.querySelectorAll(".admin")
			.forEach((x) => x.classList.toggle("d-none", user.role !== "admin"));
		await showTab("events");
	} catch (err) {
		console.error("Authentication error:", err);
		logout();
		if (err.message === "Bearer token required") {
			notice("The API did not receive your login token. Check that Apache forwards the Authorization header (backend/.htaccess).", true);
		} else {
			notice("Authentication failed: " + err.message, true);
		}
	}
}
