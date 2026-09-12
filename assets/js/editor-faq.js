/**
 * FAQ list editor helpers: style new Details as FAQ rows inside .ff-faq-list.
 */
(function (wp) {
	if (!wp || !wp.data || !wp.blocks) {
		return;
	}

	var FAQ_CLASS = "ff-faq-item";
	var LIST_CLASS = "ff-faq-list";
	var pending = {};

	function hasClass(className, token) {
		return (
			typeof className === "string" &&
			(" " + className + " ").indexOf(" " + token + " ") !== -1
		);
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
			next.metadata = Object.assign({}, meta, { name: "FAQ-Eintrag" });
			dirty = true;
		}

		pending[clientId] = true;

		if (dirty) {
			wp.data.dispatch("core/block-editor").updateBlockAttributes(clientId, next);
		}

		if (!block.innerBlocks || !block.innerBlocks.length) {
			var paragraph = wp.blocks.createBlock("core/paragraph", {
				placeholder: "Antwort hier eingeben…",
			});
			wp.data
				.dispatch("core/block-editor")
				.replaceInnerBlocks(clientId, [paragraph], false);
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
})(window.wp);
