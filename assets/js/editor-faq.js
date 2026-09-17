/**
 * FAQ list editor: variation isActive + style new Details as FAQ rows.
 */
(function (wp, config) {
	if (!wp || !wp.data || !wp.blocks) {
		return;
	}

	var FAQ_CLASS = "ff-faq-item";
	var LIST_CLASS = "ff-faq-list";
	var pending = {};
	var i18n = (config && config.i18n) || {};

	function hasClass(className, token) {
		return (
			typeof className === "string" &&
			(" " + className + " ").indexOf(" " + token + " ") !== -1
		);
	}

	function registerFaqVariation() {
		if (!wp.blocks.registerBlockVariation) {
			return;
		}
		wp.blocks.registerBlockVariation("core/details", {
			name: "fairflock-faq-item",
			title: i18n.title || "FAQ-Eintrag",
			description: i18n.description || "Frage und Antwort für die FAQ-Liste.",
			category: "text",
			icon: "editor-help",
			keywords: ["faq", "frage", "antwort", "accordion"],
			attributes: {
				className: FAQ_CLASS,
				showContent: true,
				metadata: { name: i18n.title || "FAQ-Eintrag" },
			},
			innerBlocks: [
				["core/paragraph", { placeholder: i18n.placeholder || "Antwort hier eingeben…" }],
			],
			scope: ["inserter", "block", "transform"],
			isActive: function (blockAttributes) {
				return hasClass(blockAttributes && blockAttributes.className, FAQ_CLASS);
			},
		});
	}

	if (wp.domReady) {
		wp.domReady(registerFaqVariation);
	} else {
		registerFaqVariation();
	}

	function isFaqListBlock(block) {
		return (
			block &&
			block.name === "core/group" &&
			block.attributes &&
			hasClass(block.attributes.className, LIST_CLASS)
		);
	}

	function ensureFaqItem(clientId) {
		if (pending[clientId]) {
			return;
		}

		var store = wp.data.select("core/block-editor");
		var block = store.getBlock(clientId);
		if (!block || block.name !== "core/details") {
			return;
		}

		var parentId = store.getBlockRootClientId(clientId);
		if (!parentId) {
			return;
		}

		var parent = store.getBlock(parentId);
		if (!isFaqListBlock(parent)) {
			return;
		}

		var className = block.attributes.className || "";
		var next = {};
		var dirty = false;

		if (!hasClass(className, FAQ_CLASS)) {
			next.className = (className + " " + FAQ_CLASS).trim();
			dirty = true;
		}

		var meta = block.attributes.metadata || {};
		if (!meta.name) {
			next.metadata = Object.assign({}, meta, { name: i18n.title || "FAQ-Eintrag" });
			dirty = true;
		}

		pending[clientId] = true;

		if (dirty) {
			wp.data.dispatch("core/block-editor").updateBlockAttributes(clientId, next);
		}

		if (!block.innerBlocks || !block.innerBlocks.length) {
			var paragraph = wp.blocks.createBlock("core/paragraph", {
				placeholder: i18n.placeholder || "Antwort hier eingeben…",
			});
			wp.data.dispatch("core/block-editor").replaceInnerBlocks(clientId, [paragraph], false);
		}

		setTimeout(function () {
			delete pending[clientId];
		}, 0);
	}

	var prevIds = "";
	wp.data.subscribe(function () {
		var store = wp.data.select("core/block-editor");
		if (!store || typeof store.getClientIdsWithDescendants !== "function") {
			return;
		}

		var ids = store.getClientIdsWithDescendants() || [];
		var key = ids.join(",");
		if (key === prevIds) {
			return;
		}
		prevIds = key;

		ids.forEach(function (id) {
			var block = store.getBlock(id);
			if (block && block.name === "core/details") {
				ensureFaqItem(id);
			}
		});
	});
})(window.wp, window.fairflockFaq || {});
