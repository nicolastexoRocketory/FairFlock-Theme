/**
 * Product slider/collection: Ansehen colors + image aspect (1:1 / 2:3).
 * Stored in block metadata (ffProdViewBg / ffProdViewFg / ffProdAspect).
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
	var PanelBody = wp.components.PanelBody;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var SelectControl = wp.components.SelectControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var i18n = (config && config.i18n) || {};

	var TARGET_CLASSES = [
		"ff-pattern-products--woo",
		"ff-prod-slider",
		"ff-prod-slider__track",
		"ff-shop__collection",
		"ff-pattern-products__shell",
	];

	var ASPECT_CLASS = "ff-prod-aspect--23";

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

	function setMeta(props, patch) {
		var meta = Object.assign({}, getMeta(props.attributes), patch);
		Object.keys(patch).forEach(function (key) {
			if (patch[key] === "" || patch[key] == null) {
				delete meta[key];
			}
		});
		props.setAttributes({ metadata: meta });
	}

	function setMetaColor(props, key, value) {
		var patch = {};
		patch[key] = value || "";
		setMeta(props, patch);
	}

	function syncAspectClass(props, aspect) {
		var className = props.attributes.className || "";
		var parts = String(className)
			.split(/\s+/)
			.filter(Boolean)
			.filter(function (t) {
				return t !== ASPECT_CLASS;
			});
		if (aspect === "2-3") {
			parts.push(ASPECT_CLASS);
		}
		var next = parts.join(" ");
		if (next !== className) {
			props.setAttributes({ className: next });
		}
	}

	var withProductCardControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isProductsViewTarget(props)) {
				return el(BlockEdit, props);
			}

			var meta = getMeta(props.attributes);
			var bg = meta.ffProdViewBg || undefined;
			var fg = meta.ffProdViewFg || undefined;
			var aspect = meta.ffProdAspect === "2-3" ? "2-3" : "1-1";

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					InspectorControls,
					{ group: "styles" },
					el(
						PanelBody,
						{
							title: i18n.aspectPanel || "Produktbilder",
							initialOpen: true,
						},
						el(SelectControl, {
							label: i18n.aspectLabel || "Bildformat auf Karten",
							help:
								i18n.aspectHelp ||
								"Standard 1:1. Portrait 2:3 nur für diese Sammlung.",
							value: aspect,
							options: [
								{
									label: i18n.aspect11 || "1:1 (Standard)",
									value: "1-1",
								},
								{
									label: i18n.aspect23 || "2:3 (Hochformat)",
									value: "2-3",
								},
							],
							onChange: function (value) {
								var next = value === "2-3" ? "2-3" : "1-1";
								setMeta(props, {
									ffProdAspect: next === "2-3" ? "2-3" : "",
								});
								syncAspectClass(props, next);
							},
						})
					),
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
	}, "withFairflockProductCardControls");

	wp.hooks.addFilter(
		"editor.BlockEdit",
		"fairflock/products-ansehen-controls",
		withProductCardControls
	);

	var withProductCardWrapper = createHigherOrderComponent(function (BlockListBlock) {
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
			if (meta.ffProdAspect === "2-3") {
				style["--ff-product-aspect"] = "2 / 3";
			}

			var className = (props.wrapperProps && props.wrapperProps.className) || "";
			if (meta.ffProdAspect === "2-3" && !hasClass(className, ASPECT_CLASS)) {
				className = (className ? className + " " : "") + ASPECT_CLASS;
			}

			return el(
				BlockListBlock,
				Object.assign({}, props, {
					wrapperProps: Object.assign({}, props.wrapperProps, {
						style: style,
						className: className || undefined,
					}),
				})
			);
		};
	}, "withFairflockProductCardWrapper");

	wp.hooks.addFilter(
		"editor.BlockListBlock",
		"fairflock/products-ansehen-wrapper",
		withProductCardWrapper
	);
})(window.wp, window.fairflockProductsAnsehen || {});
