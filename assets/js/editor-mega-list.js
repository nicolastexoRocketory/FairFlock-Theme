/**
 * Mega-Liste: Inspector for core/group.ff-mega-list modes.
 */
(function (wp, config) {
	if (!wp || !wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var Notice = wp.components.Notice;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var apiFetch = wp.apiFetch;
	var i18n = (config && config.i18n) || {};

	function hasClass(className, token) {
		return typeof className === "string" && (" " + className + " ").indexOf(" " + token + " ") !== -1;
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

	function stripModeAndCat(className) {
		return String(className || "")
			.replace(/(?:^|\s)ff-mega-list--(?:manual|categories|products)(?=\s|$)/g, " ")
			.replace(/(?:^|\s)ff-mega-list-cat-\d+(?=\s|$)/g, " ")
			.replace(/\s+/g, " ")
			.trim();
	}

	function buildClassName(className, mode, catId) {
		var base = stripModeAndCat(className);
		if (!hasClass(base, "ff-mega-list")) {
			base = (base + " ff-mega-list").trim();
		}
		var next = (base + " ff-mega-list--" + mode).trim();
		if (mode === "products" && catId > 0) {
			next += " ff-mega-list-cat-" + catId;
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
									className: buildClassName(className, value, catId),
								});
							},
						}),
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
})(window.wp, window.fairflockMegaList || {});
