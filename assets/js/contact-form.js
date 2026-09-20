/**
 * FairFlock contact form — AJAX submit (Delil behavior).
 */
(function () {
	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function parseJsonResponse(r) {
		return r.text().then(function (text) {
			if (!text) {
				throw new Error("empty");
			}
			try {
				return JSON.parse(text);
			} catch (e) {
				if (text === "0" || text === "-1") {
					throw new Error("session");
				}
				throw new Error("invalid");
			}
		});
	}

	function bindForm(wrap) {
		var form = wrap.querySelector(".ff-form");
		if (!form || form.getAttribute("data-ff-form-bound") === "1") {
			return;
		}
		form.setAttribute("data-ff-form-bound", "1");

		var successEl = wrap.querySelector(".ff-form__notice--success");
		var errorEl = wrap.querySelector(".ff-form__notice--error");
		var submitBtn = wrap.querySelector(".ff-form__submit");
		var nonceInput = form.querySelector('[name="fairflock_form_nonce"]');
		var cfg = window.fairflockForm || {};
		var ajaxUrl = cfg.ajaxUrl || "/wp-admin/admin-ajax.php";
		var fallbackError = cfg.errorMessage || "Leider ist ein Fehler aufgetreten.";

		function refreshNonce() {
			return fetch(ajaxUrl + "?action=fairflock_form_refresh_nonce", {
				credentials: "same-origin",
			})
				.then(function (r) {
					return r.json();
				})
				.then(function (data) {
					if (data.success && data.data && data.data.nonce && nonceInput) {
						nonceInput.value = data.data.nonce;
					}
				})
				.catch(function () {});
		}

		function resetTurnstile() {
			if (!window.turnstile) {
				return;
			}
			var widget = wrap.querySelector(".cf-turnstile");
			if (widget) {
				try {
					window.turnstile.reset(widget);
				} catch (e) {}
			}
		}

		refreshNonce();

		form.addEventListener("submit", function (e) {
			e.preventDefault();
			if (successEl) {
				successEl.hidden = true;
			}
			if (errorEl) {
				errorEl.hidden = true;
			}
			wrap.querySelectorAll(".ff-form__field.is-invalid").forEach(function (el) {
				el.classList.remove("is-invalid");
			});

			form.classList.add("is-loading");
			if (submitBtn) {
				submitBtn.disabled = true;
			}

			refreshNonce().finally(function () {
				var fd = new FormData(form);
				fd.append("action", "fairflock_form_submit");

				fetch(ajaxUrl, {
					method: "POST",
					body: fd,
					credentials: "same-origin",
				})
					.then(parseJsonResponse)
					.then(function (data) {
						if (data.success) {
							if (successEl) {
								successEl.textContent = (data.data && data.data.message) || "";
								successEl.hidden = false;
							}
							form.reset();
							resetTurnstile();
							refreshNonce();
						} else {
							if (errorEl) {
								errorEl.textContent =
									data.data && data.data.message ? data.data.message : fallbackError;
								errorEl.hidden = false;
							}
							resetTurnstile();
						}
					})
					.catch(function (err) {
						if (errorEl) {
							if (err && err.message === "session") {
								errorEl.textContent =
									"Sicherheitsprüfung fehlgeschlagen. Bitte Seite neu laden und erneut versuchen.";
							} else {
								errorEl.textContent = fallbackError;
							}
							errorEl.hidden = false;
						}
						resetTurnstile();
					})
					.finally(function () {
						form.classList.remove("is-loading");
						if (submitBtn) {
							submitBtn.disabled = false;
						}
					});
			});
		});
	}

	ready(function () {
		document.querySelectorAll(".ff-form-wrap").forEach(bindForm);
	});
})();
