/**
 * Collections section: exclude product categories via className tokens.
 * Token: ff-collections-exclude-{id}-{id}-…
 */
(function (wp, config) {
	if (
		!wp ||
		!wp.hooks ||
		!wp.compose ||
		!wp.element ||
		!wp.blockEditor ||
		!wp.components ||
		!wp.apiFetch
	) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var FormTokenField = wp.components.FormTokenField;
	var Notice = wp.components.Notice;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var apiFetch = wp.apiFetch;
	var i18n = (config && config.i18n) || {};

	function hasClass(className, token) {
		return (
			typeof className === "string" &&
			(" " + className + " ").indexOf(" " + token + " ") !== -1
		);
	}

	function isCollectionsSection(props) {
		return (
			props.name === "core/group" &&
			hasClass(props.attributes && props.attributes.className, "ff-pattern-collections")
		);
	}

	function parseExcludeIds(className) {
		var match = String(className || "").match(
			/(?:^|\s)ff-collections-exclude-((?:\d+-)*\d+)(?:\s|$)/
		);
		if (!match) {
			return [];
		}
		return match[1]
			.split("-")
			.map(function (part) {
				return parseInt(part, 10);
			})
			.filter(function (id) {
				return id > 0;
			});
	}

	function stripExcludeToken(className) {
		return String(className || "")
			.replace(/(?:^|\s)ff-collections-exclude-(?:\d+-)*\d+(?=\s|$)/g, " ")
			.replace(/\s+/g, " ")
			.trim();
	}

	function buildClassName(className, excludeIds) {
		var base = stripExcludeToken(className);
		var ids = (excludeIds || [])
			.map(function (id) {
				return parseInt(id, 10);
			})
			.filter(function (id) {
				return id > 0;
			});
		ids = Array.from(new Set(ids)).sort(function (a, b) {
			return a - b;
		});
		if (!ids.length) {
			return base;
		}
		return (base + " ff-collections-exclude-" + ids.join("-")).trim();
	}

	function tokenLabel(term) {
		return (term.name || "#" + term.id) + " · " + term.id;
	}

	function parseTokenId(token) {
		var match = String(token || "").match(/·\s*(\d+)\s*$/);
		return match ? parseInt(match[1], 10) : 0;
	}

	function CategoriesExclude(props) {
		var excludeIds = props.excludeIds || [];
		var onChange = props.onChange;
		var termsState = useState([]);
		var terms = termsState[0];
		var setTerms = termsState[1];
		var loadingState = useState(true);
		var loading = loadingState[0];
		var setLoading = loadingState[1];

		useEffect(function () {
			var cancelled = false;
			setLoading(true);
			apiFetch({
				path: "/wp/v2/product_cat?per_page=100&hide_empty=false&orderby=name&order=asc",
			})
				.then(function (list) {
					if (cancelled) {
						return;
					}
					var next = [];
					(list || []).forEach(function (term) {
						if (!term || !term.id || term.slug === "uncategorized") {
							return;
						}
						next.push({
							id: term.id,
							name: term.name || "#" + term.id,
						});
					});
					setTerms(next);
					setLoading(false);
				})
				.catch(function () {
					if (!cancelled) {
						setTerms([]);
						setLoading(false);
					}
				});
			return function () {
				cancelled = true;
			};
		}, []);

		if (loading) {
			return el("p", null, i18n.loading || "…");
		}

		var byId = {};
		terms.forEach(function (term) {
			byId[term.id] = term;
		});

		var value = excludeIds
			.map(function (id) {
				return byId[id] ? tokenLabel(byId[id]) : "#" + id + " · " + id;
			})
			.filter(Boolean);

		var suggestions = terms.map(tokenLabel);

		return el(FormTokenField, {
			label: i18n.exclude || "Kategorien ausschließen",
			value: value,
			suggestions: suggestions,
			onChange: function (tokens) {
				var ids = [];
				(tokens || []).forEach(function (token) {
					var id = parseTokenId(token);
					if (!id) {
						var found = terms.find(function (term) {
							return tokenLabel(term) === token || term.name === token;
						});
						id = found ? found.id : 0;
					}
					if (id > 0) {
						ids.push(id);
					}
				});
				onChange(ids);
			},
			__experimentalExpandOnFocus: true,
			__experimentalShowHowTo: false,
		});
	}

	var withCollectionsControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isCollectionsSection(props)) {
				return el(BlockEdit, props);
			}

			var className = props.attributes.className || "";
			var excludeIds = parseExcludeIds(className);

			return el(
				Fragment,
				null,
				el(BlockEdit, props),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: i18n.panel || "Kollektionen",
							initialOpen: true,
						},
						el(CategoriesExclude, {
							excludeIds: excludeIds,
							onChange: function (ids) {
								props.setAttributes({
									className: buildClassName(className, ids),
								});
							},
						}),
						el(
							Notice,
							{
								status: "info",
								isDismissible: false,
							},
							i18n.hint ||
								"Ausgeschlossene Kategorien erscheinen auf der Website nicht. Die Karten im Editor dienen als Platzhalter."
						)
					)
				)
			);
		};
	}, "withFairflockCollectionsControls");

	wp.hooks.addFilter(
		"editor.BlockEdit",
		"fairflock/collections-exclude",
		withCollectionsControls
	);
})(window.wp, window.fairflockCollections || {});
