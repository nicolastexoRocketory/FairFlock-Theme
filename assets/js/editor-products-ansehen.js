/**
 * Product slider: color controls for the CSS “Ansehen” hover button.
 * Stored in block metadata (ffProdViewBg / ffProdViewFg), applied as CSS vars.
 */
(function (wp, config) {
	if (
		!wp ||
		!wp.hooks ||
		!wp.compose ||
		!wp.element ||
		!wp.blockEditor ||
		!wp.components
	) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var i18n = (config && config.i18n) || {};

	var TARGET_CLASSES = [
		"ff-pattern-products--woo",
		"ff-prod-slider",
		"ff-prod-slider__track",
		"ff-shop__collection",
	];

	function hasClass(className, token) {
		return (
			typeof className === "string" &&
			(" " + className + " ").indexOf(" " + token + " ") !== -1
		);
	}

	function isProductsViewTarget(props) {
		var className = props.attributes && props.attributes.className;
		if (!className) {
			return false;
		}
		for (var i = 0; i < TARGET_CLASSES.length; i++) {
			if (hasClass(className, TARGET_CLASSES[i])) {
				return true;
			}
		}
		return false;
	}

	function getMeta(attributes) {
		return (attributes && attributes.metadata) || {};
	}

	function setMetaColor(props, key, value) {
		var meta = Object.assign({}, getMeta(props.attributes));
		if (value) {
			meta[key] = value;
		} else {
			delete meta[key];
		}
		props.setAttributes({ metadata: meta });
	}

	var withAnsehenControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isProductsViewTarget(props)) {
				return el(BlockEdit, props);
			}

			var meta = getMeta(props.attributes);
			var bg = meta.ffProdViewBg || undefined;
			var fg = meta.ffProdViewFg || undefined;

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					InspectorControls,
					{ group: "styles" },
					el(PanelColorSettings, {
						title: i18n.panel || "Ansehen-Button",
						initialOpen: false,
						colorSettings: [
							{
								value: bg,
								onChange: function (color) {
									setMetaColor(props, "ffProdViewBg", color);
								},
								label: i18n.background || "Hintergrund",
							},
							{
								value: fg,
								onChange: function (color) {
									setMetaColor(props, "ffProdViewFg", color);
								},
								label: i18n.text || "Text",
							},
						],
					})
				)
			);
		};
	}, "withFairflockAnsehenControls");

	wp.hooks.addFilter(
		"editor.BlockEdit",
		"fairflock/products-ansehen-controls",
		withAnsehenControls
	);

	var withAnsehenWrapper = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (!isProductsViewTarget(props)) {
				return el(BlockListBlock, props);
			}

			var meta = getMeta(props.attributes);
			var style = Object.assign({}, (props.wrapperProps && props.wrapperProps.style) || {});
			if (meta.ffProdViewBg) {
				style["--ff-prod-view-bg"] = meta.ffProdViewBg;
			}
			if (meta.ffProdViewFg) {
				style["--ff-prod-view-fg"] = meta.ffProdViewFg;
			}

			return el(
				BlockListBlock,
				Object.assign({}, props, {
					wrapperProps: Object.assign({}, props.wrapperProps, { style: style }),
				})
			);
		};
	}, "withFairflockAnsehenWrapper");

	wp.hooks.addFilter(
		"editor.BlockListBlock",
		"fairflock/products-ansehen-wrapper",
		withAnsehenWrapper
	);
})(window.wp, window.fairflockProductsAnsehen || {});
