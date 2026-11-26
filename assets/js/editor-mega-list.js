/**
 * Mega-Liste: Variation + Inspector for core/group.ff-mega-list modes.
 *
 * Variation must be registered in JS — PHP core/group variations are
 * overwritten by the Group block’s client variations (Trac #60856).
 */
(function (wp, config) {
	if (!wp || !wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components || !wp.blocks) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Notice = wp.components.Notice;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var apiFetch = wp.apiFetch;
	var i18n = (config && config.i18n) || {};

	function hasClass(className, token) {
		return typeof className === "string" && (" " + className + " ").indexOf(" " + token + " ") !== -1;
	}

	function registerMegaListVariation() {
		wp.blocks.registerBlockVariation("core/group", {
			name: "fairflock-mega-list",
			title: i18n.panel || "Mega-Liste",
			description:
				i18n.variationDesc ||
				"Liste im Mega-Menü: manuell, alle Kategorien oder Produkte einer Kategorie.",
			category: "theme",
			icon: "menu",
			keywords: ["mega", "liste", "kategorie", "produkte", "menü", "fairflock"],
			attributes: {
				className: "ff-mega-list ff-mega-list--manual",
				layout: { type: "default" },
				metadata: { name: i18n.panel || "Mega-Liste" },
			},
			innerBlocks: [
				[
					"core/list",
					{ className: "ff-mega-panel__links", fontSize: "small" },
					[["core/list-item", { content: i18n.exampleLink || "Beispiel-Link" }]],
				],
			],
			scope: ["inserter", "block", "transform"],
			isActive: function (blockAttributes) {
				return hasClass(blockAttributes && blockAttributes.className, "ff-mega-list");
			},
		});
	}

	if (wp.domReady) {
		wp.domReady(registerMegaListVariation);
	} else {
		registerMegaListVariation();
	}

	function parseMode(className) {
		if (hasClass(className, "ff-mega-list--categories")) {
			return "categories";
		}
		if (hasClass(className, "ff-mega-list--products")) {
			return "products";
		}
		return "manual";
	}

	function parseCategoryId(className) {
		var match = String(className || "").match(/(?:^|\s)ff-mega-list-cat-(\d+)(?:\s|$)/);
		return match ? parseInt(match[1], 10) : 0;
	}

	function hasParentsOnly(className) {
		return hasClass(className, "ff-mega-list--parents");
	}

	function stripModeAndCat(className) {
		return String(className || "")
			.replace(/(?:^|\s)ff-mega-list--(?:manual|categories|products)(?=\s|$)/g, " ")
			.replace(/(?:^|\s)ff-mega-list-cat-\d+(?=\s|$)/g, " ")
			.replace(/(?:^|\s)ff-mega-list--parents(?=\s|$)/g, " ")
			.replace(/\s+/g, " ")
			.trim();
	}

	function buildClassName(className, mode, catId, parentsOnly) {
		var base = stripModeAndCat(className);
		if (!hasClass(base, "ff-mega-list")) {
			base = (base + " ff-mega-list").trim();
		}
		var next = (base + " ff-mega-list--" + mode).trim();
		if (mode === "products" && catId > 0) {
			next += " ff-mega-list-cat-" + catId;
		}
		if (mode === "categories" && parentsOnly) {
			next += " ff-mega-list--parents";
		}
		return next;
	}

	function isMegaList(props) {
		return props.name === "core/group" && hasClass(props.attributes.className, "ff-mega-list");
	}

	function CategorySelect(props) {
		var catId = props.catId;
		var onChange = props.onChange;
		var state = useState([]);
		var options = state[0];
		var setOptions = state[1];
		var loadingState = useState(true);
		var loading = loadingState[0];
		var setLoading = loadingState[1];

		useEffect(function () {
			var cancelled = false;
			setLoading(true);
			apiFetch({ path: "/wp/v2/product_cat?per_page=100&hide_empty=false&orderby=name&order=asc" })
				.then(function (terms) {
					if (cancelled) {
						return;
					}
					var opts = [
						{ label: i18n.noCategory || "— Kategorie wählen —", value: "0" },
					];
					(terms || []).forEach(function (term) {
						if (!term || !term.id) {
							return;
						}
						if (term.slug === "uncategorized") {
							return;
						}
						opts.push({
							label: term.name || "#" + term.id,
							value: String(term.id),
						});
					});
					setOptions(opts);
					setLoading(false);
				})
				.catch(function () {
					if (!cancelled) {
						setOptions([{ label: i18n.noCategory || "—", value: "0" }]);
						setLoading(false);
					}
				});
			return function () {
				cancelled = true;
			};
		}, []);

		if (loading) {
			return el("p", { className: "ff-mega-list__loading" }, i18n.loading || "…");
		}

		return el(SelectControl, {
			label: i18n.category || "Kategorie",
			help: i18n.categoryHelp || "",
			value: String(catId || 0),
			options: options,
			onChange: function (value) {
				onChange(parseInt(value, 10) || 0);
			},
		});
	}

	var withMegaListControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isMegaList(props)) {
				return el(BlockEdit, props);
			}

			var className = props.attributes.className || "";
			var mode = parseMode(className);
			var catId = parseCategoryId(className);
			var parentsOnly = mode === "categories" && hasParentsOnly(className);
			var hint =
				mode === "categories"
					? i18n.hintCats
					: mode === "products"
					? i18n.hintProds
					: i18n.hintManual;

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: i18n.panel || "Mega-Liste", initialOpen: true },
						el(SelectControl, {
							label: i18n.mode || "Inhalt",
							value: mode,
							options: [
								{ label: i18n.manual || "Manuell", value: "manual" },
								{ label: i18n.categories || "Alle Kategorien", value: "categories" },
								{ label: i18n.products || "Produkte einer Kategorie", value: "products" },
							],
							onChange: function (value) {
								props.setAttributes({
									className: buildClassName(
										className,
										value,
										catId,
										value === "categories" && parentsOnly
									),
								});
							},
						}),
						mode === "categories"
							? el(ToggleControl, {
									label: i18n.parents || "Nur Überkategorien",
									help:
										i18n.parentsHelp ||
										"Nur Hauptkategorien. Ein Klick öffnet die Unterkategorien, oben mit Zurück.",
									checked: parentsOnly,
									onChange: function (next) {
										props.setAttributes({
											className: buildClassName(className, "categories", catId, !!next),
										});
									},
							  })
							: null,
						mode === "products"
							? el(CategorySelect, {
									catId: catId,
									onChange: function (nextId) {
										props.setAttributes({
											className: buildClassName(className, "products", nextId),
										});
									},
							  })
							: null,
						hint
							? el(
									Notice,
									{ status: "info", isDismissible: false },
									hint
							  )
							: null
					)
				)
			);
		};
	}, "withFairflockMegaListControls");

	wp.hooks.addFilter("editor.BlockEdit", "fairflock/mega-list-controls", withMegaListControls);

	/* —— Cover-Link + Zoom (WP 7 Cover hat kein natives Link-Icon) —— */
	wp.hooks.addFilter(
		"blocks.registerBlockType",
		"fairflock/mega-cover-link-attr",
		function (settings, name) {
			if (name !== "core/cover") {
				return settings;
			}
			settings.attributes = Object.assign({}, settings.attributes, {
				ffMegaLink: { type: "string", default: "" },
			});
			return settings;
		}
	);

	var TextControl = wp.components.TextControl;
	var BlockControls = wp.blockEditor.BlockControls;
	var LinkControl = wp.blockEditor.LinkControl;
	var ToolbarButton = wp.components.ToolbarButton;
	var Popover = wp.components.Popover;
	var ToolbarGroup = wp.components.ToolbarGroup;

	var withMegaCoverLink = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== "core/cover") {
				return el(BlockEdit, props);
			}

			var attrs = props.attributes || {};
			var className = attrs.className || "";
			var link = typeof attrs.ffMegaLink === "string" ? attrs.ffMegaLink : "";
			var isMegaMedia = hasClass(className, "ff-mega-panel__card-media");
			var isLinked = !!link;

			var openState = useState(false);
			var isOpen = openState[0];
			var setOpen = openState[1];

			function setLink(next) {
				var url = next && next.url ? String(next.url) : "";
				props.setAttributes({ ffMegaLink: url });
				if (!url) {
					setOpen(false);
				}
			}

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					BlockControls,
					{ group: "block" },
					el(
						ToolbarGroup,
						null,
						el(ToolbarButton, {
							icon: isLinked ? "admin-links" : "admin-links",
							label: isLinked
								? i18n.coverLinkEdit || "Cover-Link bearbeiten"
								: i18n.coverLinkAdd || "Cover verlinken",
							onClick: function () {
								setOpen(!isOpen);
							},
							isPressed: isOpen || isLinked,
						}),
						isOpen
							? el(
									Popover,
									{
										placement: "bottom-start",
										onClose: function () {
											setOpen(false);
										},
										offset: 8,
									},
									el(
										"div",
										{
											style: {
												minWidth: 320,
												padding: 8,
											},
										},
										el(LinkControl, {
											value: link ? { url: link } : undefined,
											onChange: setLink,
											onRemove: function () {
												setLink({ url: "" });
											},
											hasTextControl: false,
											settings: [],
										})
									)
							  )
							: null
					)
				),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: i18n.coverLinkPanel || "Cover-Link",
							initialOpen: isMegaMedia || isLinked,
						},
						el(TextControl, {
							label: i18n.coverLinkLabel || "URL",
							value: link,
							placeholder: i18n.coverLinkPlaceholder || "/shop/ oder https://…",
							onChange: function (value) {
								props.setAttributes({ ffMegaLink: value || "" });
							},
							help:
								i18n.coverLinkHelp ||
								"Cover verlinken. Mit Link: Zoom-In beim Hover im Frontend.",
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						})
					)
				)
			);
		};
	}, "withFairflockMegaCoverLink");

	wp.hooks.addFilter("editor.BlockEdit", "fairflock/mega-cover-link", withMegaCoverLink);
})(window.wp, window.fairflockMegaList || {});
