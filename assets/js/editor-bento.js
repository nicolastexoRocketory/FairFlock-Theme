/**
 * Bento cards: column/row span controls (className API → CSS grid).
 * Only .ff-bento-card children; strips legacy is-wide/is-tall.
 */
(function (wp, config) {
	if (!wp || !wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components || !wp.data) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var i18n = (config && config.i18n) || {};

	function hasClass(className, token) {
		return typeof className === "string" && (" " + className + " ").indexOf(" " + token + " ") !== -1;
	}

	function parseSpan(className, prefix) {
		var match = String(className || "").match(new RegExp("(?:^|\\s)" + prefix + "-(\\d+)(?:\\s|$)"));
		return match ? parseInt(match[1], 10) : 0;
	}

	function setSpanClass(className, prefix, value) {
		var cleaned = String(className || "")
			.replace(new RegExp("(?:^|\\s)" + prefix + "-\\d+(?=\\s|$)", "g"), " ")
			.replace(/(?:^|\s)is-wide(?=\s|$)/g, " ")
			.replace(/(?:^|\s)is-tall(?=\s|$)/g, " ")
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

	function isBentoCard(props) {
		return (
			props.name === "core/group" &&
			hasClass(props.attributes && props.attributes.className, "ff-bento-card")
		);
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
			if (!isBentoCard(props)) {
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
						{ title: i18n.panel || "Bento-Raster", initialOpen: true },
						el(RangeControl, {
							label: i18n.cols || "Spalten",
							min: 1,
							max: 4,
							value: spans.cols,
							onChange: function (value) {
								props.setAttributes({
									className: setSpanClass(className, "ff-bento-cols", value),
								});
							},
						}),
						el(RangeControl, {
							label: i18n.rows || "Zeilen",
							min: 1,
							max: 3,
							value: spans.rows,
							onChange: function (value) {
								props.setAttributes({
									className: setSpanClass(className, "ff-bento-rows", value),
								});
							},
						})
					)
				)
			);
		};
	}, "withBentoSpan");

	wp.hooks.addFilter("editor.BlockEdit", "fairflock/bento-span", withBentoSpan);
})(window.wp, window.fairflockBento || {});
