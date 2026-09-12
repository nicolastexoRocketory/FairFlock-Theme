/**
 * Bento cards: column/row span controls in the inspector.
 */
(function (wp) {
	if (!wp || !wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components || !wp.data) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;

	function hasClass(className, token) {
		return typeof className === "string" && (" " + className + " ").indexOf(" " + token + " ") !== -1;
	}

	function parseSpan(className, prefix) {
		var match = String(className || "").match(new RegExp(prefix + "-(\\d)"));
		return match ? parseInt(match[1], 10) : 0;
	}

	function setSpanClass(className, prefix, value) {
		var cleaned = String(className || "")
			.replace(new RegExp("(?:^|\\s)" + prefix + "-\\d", "g"), " ")
			.replace(/\s+/g, " ")
			.trim();
		return (cleaned + " " + prefix + "-" + value).trim();
	}

	function mosaicDefaults(index) {
		var n = ((index % 5) + 5) % 5;
		if (n === 0) {
			return { cols: 1, rows: 2 };
		}
		if (n === 1 || n === 4) {
			return { cols: 2, rows: 1 };
		}
		return { cols: 1, rows: 1 };
	}

	function isBentoChild(props) {
		if (props.name !== "core/group") {
			return false;
		}
		if (hasClass(props.attributes.className, "ff-bento-card")) {
			return true;
		}
		var parentId = wp.data.select("core/block-editor").getBlockRootClientId(props.clientId);
		if (!parentId) {
			return false;
		}
		var parent = wp.data.select("core/block-editor").getBlock(parentId);
		return !!(parent && parent.attributes && hasClass(parent.attributes.className, "ff-bento"));
	}

	function currentSpans(props) {
		var className = props.attributes.className || "";
		var cols = parseSpan(className, "ff-bento-cols");
		var rows = parseSpan(className, "ff-bento-rows");
		if (cols && rows) {
			return { cols: cols, rows: rows };
		}
		if (hasClass(className, "is-wide")) {
			cols = cols || 2;
		}
		if (hasClass(className, "is-tall")) {
			rows = rows || 2;
		}
		if (!cols || !rows) {
			var index = wp.data.select("core/block-editor").getBlockIndex(props.clientId);
			var fallback = mosaicDefaults(typeof index === "number" ? index : 0);
			cols = cols || fallback.cols;
			rows = rows || fallback.rows;
		}
		return { cols: cols, rows: rows };
	}

	var withBentoSpan = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isBentoChild(props)) {
				return el(BlockEdit, props);
			}

			var spans = currentSpans(props);
			var className = props.attributes.className || "";

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: "Bento-Raster", initialOpen: true },
						el(RangeControl, {
							label: "Spalten",
							min: 1,
							max: 4,
							value: spans.cols,
							onChange: function (value) {
								var next = setSpanClass(className, "ff-bento-cols", value);
								var style = Object.assign({}, props.attributes.style || {});
								style.layout = Object.assign({}, style.layout || {}, { columnSpan: value });
								props.setAttributes({ className: next, style: style });
							},
						}),
						el(RangeControl, {
							label: "Zeilen",
							min: 1,
							max: 3,
							value: spans.rows,
							onChange: function (value) {
								var next = setSpanClass(className, "ff-bento-rows", value);
								var style = Object.assign({}, props.attributes.style || {});
								style.layout = Object.assign({}, style.layout || {}, { rowSpan: value });
								props.setAttributes({ className: next, style: style });
							},
						})
					)
				)
			);
		};
	}, "withBentoSpan");

	wp.hooks.addFilter("editor.BlockEdit", "fairflock/bento-span", withBentoSpan);
})(window.wp);
