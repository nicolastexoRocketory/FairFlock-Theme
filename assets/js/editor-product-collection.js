/**
 * FairFlock product slideshow editor guards.
 *
 * - Lock displayLayout to flex (grid track / horizontal slider CSS)
 * - Hide Layout inspector control
 * - After “Sammlung wählen”, Woo replaces inner blocks + adds a heading;
 *   restore FairFlock card template and strip collection chrome headings
 */
(function (wp) {
	if (!wp || !wp.data || !wp.domReady || !wp.blocks) {
		return;
	}

	var LOCKED_TYPE = "flex";
	var CLASS_TOKEN = "ff-prod-slider__track";
	var CLASS_SHOP = "ff-shop__collection";
	var BODY_CLASS = "ff-hide-pc-layout";
	var busy = false;
	var lastSignature = {};

	function hasClass(className, token) {
		return (
			typeof className === "string" &&
			(" " + className + " ").indexOf(" " + token + " ") !== -1
		);
	}

	function ensureSliderClasses(className) {
		var parts = String(className || "")
			.split(/\s+/)
			.filter(Boolean);
		if (parts.indexOf(CLASS_SHOP) === -1) {
			parts.push(CLASS_SHOP);
		}
		if (parts.indexOf(CLASS_TOKEN) === -1) {
			parts.push(CLASS_TOKEN);
		}
		return parts.join(" ");
	}

	function isInsideFairflockProductsSection(clientId) {
		var select = wp.data.select("core/block-editor");
		if (!select || !clientId) {
			return false;
		}
		var parentId = select.getBlockRootClientId(clientId);
		while (parentId) {
			var parent = select.getBlock(parentId);
			if (!parent) {
				break;
			}
			var cls = parent.attributes && parent.attributes.className;
			if (
				hasClass(cls, "ff-pattern-products--woo") ||
				hasClass(cls, "ff-prod-slider") ||
				hasClass(cls, "ff-pattern-products")
			) {
				return true;
			}
			parentId = select.getBlockRootClientId(parentId);
		}
		return false;
	}

	function isFairflockProductSlider(block) {
		if (!block || block.name !== "woocommerce/product-collection") {
			return false;
		}
		if (hasClass(block.attributes && block.attributes.className, CLASS_TOKEN)) {
			return true;
		}
		// After “Sammlung wählen” Woo may wipe className — recover via parent section.
		return isInsideFairflockProductsSection(block.clientId);
	}

	function isProductSummary(block) {
		if (!block || !block.name) {
			return false;
		}
		if (block.name === "woocommerce/product-summary") {
			return true;
		}
		if (
			block.name === "core/post-excerpt" &&
			block.attributes &&
			(block.attributes.__woocommerceNamespace ===
				"woocommerce/product-collection/product-summary" ||
				block.attributes.__woocommerceNamespace ===
					"woocommerce/product-query/product-summary")
		) {
			return true;
		}
		return false;
	}

	function isCollectionChromeHeading(block) {
		return block && block.name === "core/heading";
	}

	function buildFairflockCardTemplate() {
		var createBlock = wp.blocks.createBlock;
		return [
			createBlock("woocommerce/product-template", {}, [
				createBlock("woocommerce/product-image", {
					showSaleBadge: false,
					imageSizing: "thumbnail",
					isDescendentOfQueryLoop: true,
					className: "ff-prod-card__media",
				}),
				createBlock(
					"core/group",
					{
						className: "ff-prod-card__body",
						style: {
							spacing: {
								blockGap: "6px",
								padding: {
									top: "var:preset|spacing|20",
									bottom: "var:preset|spacing|20",
									left: "var:preset|spacing|20",
									right: "var:preset|spacing|20",
								},
							},
						},
						layout: { type: "default" },
					},
					[
						createBlock("core/post-title", {
							textAlign: "left",
							level: 3,
							isLink: true,
							fontSize: "medium",
							__woocommerceNamespace:
								"woocommerce/product-collection/product-title",
						}),
						createBlock("woocommerce/product-price", {
							textAlign: "left",
							isDescendentOfQueryLoop: true,
							fontSize: "medium",
							className: "ff-prod-card__price",
						}),
					]
				),
			]),
		];
	}

	function findProductTemplate(blocks) {
		if (!Array.isArray(blocks)) {
			return null;
		}
		for (var i = 0; i < blocks.length; i++) {
			if (blocks[i] && blocks[i].name === "woocommerce/product-template") {
				return blocks[i];
			}
			var nested = findProductTemplate(blocks[i].innerBlocks || []);
			if (nested) {
				return nested;
			}
		}
		return null;
	}

	function stripSummariesDeep(blocks) {
		if (!Array.isArray(blocks) || !blocks.length) {
			return { blocks: blocks || [], changed: false };
		}
		var changed = false;
		var next = [];
		blocks.forEach(function (block) {
			if (isProductSummary(block)) {
				changed = true;
				return;
			}
			var inner = stripSummariesDeep(block.innerBlocks || []);
			if (inner.changed) {
				changed = true;
				next.push(
					Object.assign({}, block, {
						innerBlocks: inner.blocks,
					})
				);
				return;
			}
			next.push(block);
		});
		return { blocks: next, changed: changed };
	}

	function normalizeSliderInnerBlocks(innerBlocks) {
		var list = Array.isArray(innerBlocks) ? innerBlocks.slice() : [];
		var changed = false;
		var filtered = [];

		list.forEach(function (block) {
			// Woo collection presets inject a section heading above the products.
			if (isCollectionChromeHeading(block)) {
				changed = true;
				return;
			}
			filtered.push(block);
		});

		var template = findProductTemplate(filtered);
		if (!template) {
			return { blocks: buildFairflockCardTemplate(), changed: true };
		}

		var stripped = stripSummariesDeep(filtered);
		if (stripped.changed) {
			changed = true;
			filtered = stripped.blocks;
		}

		// If Woo swapped to a multi-block chrome layout (heading + template + …),
		// keep only the product template and re-wrap with FairFlock card markup.
		var onlyTemplate =
			filtered.length === 1 &&
			filtered[0] &&
			filtered[0].name === "woocommerce/product-template";

		if (!onlyTemplate) {
			template = findProductTemplate(filtered);
			if (template) {
				var cleanedTemplate = stripSummariesDeep([template]);
				var kids = cleanedTemplate.blocks[0].innerBlocks || [];
				var hasBody = kids.some(function (b) {
					return (
						b &&
						b.name === "core/group" &&
						hasClass(b.attributes && b.attributes.className, "ff-prod-card__body")
					);
				});
				if (!hasBody) {
					return { blocks: buildFairflockCardTemplate(), changed: true };
				}
				return {
					blocks: [cleanedTemplate.blocks[0]],
					changed: true,
				};
			}
			return { blocks: buildFairflockCardTemplate(), changed: true };
		}

		var bodyOk = (filtered[0].innerBlocks || []).some(function (b) {
			return (
				b &&
				b.name === "core/group" &&
				hasClass(b.attributes && b.attributes.className, "ff-prod-card__body")
			);
		});
		if (!bodyOk) {
			return { blocks: buildFairflockCardTemplate(), changed: true };
		}

		return { blocks: filtered, changed: changed };
	}

	function signatureFor(block) {
		var a = block.attributes || {};
		var layout = a.displayLayout || {};
		function deepNames(blocks, depth) {
			depth = depth || 0;
			if (!blocks || depth > 4) {
				return [];
			}
			var out = [];
			blocks.forEach(function (b) {
				if (!b) {
					return;
				}
				out.push(b.name || "");
				if (b.innerBlocks && b.innerBlocks.length) {
					out = out.concat(deepNames(b.innerBlocks, depth + 1));
				}
			});
			return out;
		}
		return [
			a.collection || "",
			JSON.stringify(a.query || {}),
			layout.type || "",
			a.className || "",
			deepNames(block.innerBlocks || []).join(","),
		].join("|");
	}

	function repairSliderBlock(clientId) {
		var select = wp.data.select("core/block-editor");
		var dispatch = wp.data.dispatch("core/block-editor");
		var block = select.getBlock(clientId);
		if (!isFairflockProductSlider(block)) {
			return;
		}

		var attrs = block.attributes || {};
		var layout = attrs.displayLayout || {};
		var nextClass = ensureSliderClasses(attrs.className);
		var attrPatch = {};
		var needsAttrs = false;

		if (
			layout.type !== LOCKED_TYPE ||
			typeof layout.columns !== "number" ||
			layout.shrinkColumns !== true
		) {
			attrPatch.displayLayout = {
				type: LOCKED_TYPE,
				columns: typeof layout.columns === "number" ? layout.columns : 4,
				shrinkColumns: true,
			};
			needsAttrs = true;
		}

		if (nextClass !== attrs.className) {
			attrPatch.className = nextClass;
			needsAttrs = true;
		}

		if (needsAttrs) {
			dispatch.updateBlockAttributes(clientId, attrPatch);
			block = select.getBlock(clientId);
		}

		var normalized = normalizeSliderInnerBlocks(block.innerBlocks || []);
		if (normalized.changed) {
			dispatch.replaceInnerBlocks(clientId, normalized.blocks, false);
		}
	}

	function syncAllSliders() {
		if (busy) {
			return;
		}
		var select = wp.data.select("core/block-editor");
		var dispatch = wp.data.dispatch("core/block-editor");
		if (!select || !dispatch) {
			return;
		}

		var selected = select.getSelectedBlock();
		document.body.classList.toggle(
			BODY_CLASS,
			isFairflockProductSlider(selected)
		);

		var blocks = select.getBlocks();
		var stack = blocks.slice();
		var sliders = [];

		while (stack.length) {
			var b = stack.shift();
			if (!b) {
				continue;
			}
			if (isFairflockProductSlider(b)) {
				sliders.push(b);
			}
			if (b.innerBlocks && b.innerBlocks.length) {
				stack = stack.concat(b.innerBlocks);
			}
		}

		busy = true;
		try {
			sliders.forEach(function (block) {
				var clientId = block.clientId;
				var sig = signatureFor(block);
				if (lastSignature[clientId] === sig) {
					// Still run repair when selected — layout toggles need immediate lock.
					if (!(selected && selected.clientId === clientId)) {
						return;
					}
				}
				repairSliderBlock(clientId);
				var after = select.getBlock(clientId);
				if (after) {
					lastSignature[clientId] = signatureFor(after);
				}
			});
		} finally {
			busy = false;
		}
	}

	wp.domReady(function () {
		wp.data.subscribe(syncAllSliders);
		syncAllSliders();
	});
})(window.wp);
