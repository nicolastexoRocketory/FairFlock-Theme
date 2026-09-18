/**
 * Bento: card spans + grid content source (className API).
 * Cards: .ff-bento-card → cols/rows.
 * Grid: .ff-bento → manual | collections | products | posts.
 */
(function (wp, config) {
	if (
		!wp ||
		!wp.hooks ||
		!wp.compose ||
		!wp.element ||
		!wp.blockEditor ||
		!wp.components ||
		!wp.data ||
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
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var Notice = wp.components.Notice;
	var FormTokenField = wp.components.FormTokenField;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var apiFetch = wp.apiFetch;
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

	function isBentoGrid(props) {
		var className = props.attributes && props.attributes.className;
		return (
			props.name === "core/group" &&
			hasClass(className, "ff-bento") &&
			!hasClass(className, "ff-bento-card")
		);
	}

	function parseMode(className) {
		if (hasClass(className, "ff-bento--collections")) {
			return "collections";
		}
		if (hasClass(className, "ff-bento--products")) {
			return "products";
		}
		if (hasClass(className, "ff-bento--posts")) {
			return "posts";
		}
		return "manual";
	}

	function parseCategoryId(className) {
		var match = String(className || "").match(/(?:^|\s)ff-bento-cat-(\d+)(?:\s|$)/);
		return match ? parseInt(match[1], 10) : 0;
	}

	function parsePostIds(className) {
		var match = String(className || "").match(/(?:^|\s)ff-bento-ids-((?:\d+-)*\d+)(?:\s|$)/);
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
			})
			.slice(0, 12);
	}

	function stripSourceTokens(className) {
		return String(className || "")
			.replace(/(?:^|\s)ff-bento--(?:manual|collections|products|posts)(?=\s|$)/g, " ")
			.replace(/(?:^|\s)ff-bento-cat-\d+(?=\s|$)/g, " ")
			.replace(/(?:^|\s)ff-bento-ids-(?:\d+-)*\d+(?=\s|$)/g, " ")
			.replace(/\s+/g, " ")
			.trim();
	}

	function buildGridClassName(className, mode, catId, postIds) {
		var base = stripSourceTokens(className);
		if (!hasClass(base, "ff-bento")) {
			base = (base + " ff-bento").trim();
		}
		var next = (base + " ff-bento--" + mode).trim();
		if (mode === "products" && catId > 0) {
			next += " ff-bento-cat-" + catId;
		}
		if (mode === "posts" && postIds && postIds.length) {
			next += " ff-bento-ids-" + postIds.slice(0, 12).join("-");
		}
		return next;
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
					var opts = [{ label: i18n.noCategory || "— Kategorie wählen —", value: "0" }];
					(terms || []).forEach(function (term) {
						if (!term || !term.id || term.slug === "uncategorized") {
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
			return el("p", null, i18n.loadingCats || "…");
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

	function tokenLabel(item) {
		return (item.title || "#" + item.id) + " · " + item.id;
	}

	function plainTitle(rendered, fallbackId) {
		var raw = String(rendered || "");
		if (!raw) {
			return "#" + fallbackId;
		}
		if (typeof document !== "undefined") {
			var tmp = document.createElement("textarea");
			tmp.innerHTML = raw.replace(/<[^>]+>/g, "");
			return tmp.value || "#" + fallbackId;
		}
		return raw.replace(/<[^>]+>/g, "") || "#" + fallbackId;
	}

	function parseTokenId(token) {
		var match = String(token || "").match(/·\s*(\d+)\s*$/);
		return match ? parseInt(match[1], 10) : 0;
	}

	function PostsSelect(props) {
		var postIds = props.postIds || [];
		var onChange = props.onChange;
		var itemsState = useState([]);
		var items = itemsState[0];
		var setItems = itemsState[1];
		var loadingState = useState(true);
		var loading = loadingState[0];
		var setLoading = loadingState[1];

		useEffect(function () {
			var cancelled = false;
			setLoading(true);
			Promise.all([
				apiFetch({ path: "/wp/v2/pages?per_page=50&orderby=title&order=asc&status=publish" }),
				apiFetch({ path: "/wp/v2/posts?per_page=50&orderby=date&order=desc&status=publish" }),
			])
				.then(function (results) {
					if (cancelled) {
						return;
					}
					var next = [];
					(results[0] || []).forEach(function (page) {
						if (page && page.id) {
							next.push({
								id: page.id,
								title: plainTitle(page.title && page.title.rendered, page.id),
								type: "page",
							});
						}
					});
					(results[1] || []).forEach(function (post) {
						if (post && post.id) {
							next.push({
								id: post.id,
								title: plainTitle(post.title && post.title.rendered, post.id),
								type: "post",
							});
						}
					});
					setItems(next);
					setLoading(false);
				})
				.catch(function () {
					if (!cancelled) {
						setItems([]);
						setLoading(false);
					}
				});
			return function () {
				cancelled = true;
			};
		}, []);

		var byId = {};
		items.forEach(function (item) {
			byId[item.id] = item;
		});

		var value = postIds
			.map(function (id) {
				if (byId[id]) {
					return tokenLabel(byId[id]);
				}
				return "#" + id + " · " + id;
			})
			.filter(Boolean);

		var suggestions = items.map(tokenLabel);

		if (loading) {
			return el("p", null, i18n.loadingPosts || "…");
		}

		return el(FormTokenField, {
			label: i18n.posts || "Seiten / Beiträge",
			value: value,
			suggestions: suggestions,
			maxLength: 12,
			onChange: function (tokens) {
				var ids = [];
				var seen = {};
				(tokens || []).forEach(function (token) {
					var id = parseTokenId(token);
					if (id > 0 && !seen[id]) {
						seen[id] = true;
						ids.push(id);
					}
				});
				onChange(ids.slice(0, 12));
			},
			__experimentalExpandOnFocus: true,
			__experimentalShowHowTo: false,
		});
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

	var withBentoContent = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isBentoGrid(props)) {
				return el(BlockEdit, props);
			}

			var className = props.attributes.className || "";
			var mode = parseMode(className);
			var catId = parseCategoryId(className);
			var postIds = parsePostIds(className);
			var hint =
				mode === "collections"
					? i18n.hintCollections
					: mode === "products"
					? i18n.hintProducts
					: mode === "posts"
					? i18n.hintPosts
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
						{ title: i18n.contentPanel || "Bento-Inhalt", initialOpen: true },
						el(SelectControl, {
							label: i18n.mode || "Inhalt",
							value: mode,
							options: [
								{ label: i18n.manual || "Manuell", value: "manual" },
								{ label: i18n.collections || "Kollektionen (live)", value: "collections" },
								{ label: i18n.products || "Produkte einer Kategorie", value: "products" },
								{ label: i18n.postsMode || "Seiten / Beiträge", value: "posts" },
							],
							onChange: function (value) {
								props.setAttributes({
									className: buildGridClassName(className, value, catId, postIds),
								});
							},
						}),
						mode === "products"
							? el(CategorySelect, {
									catId: catId,
									onChange: function (nextId) {
										props.setAttributes({
											className: buildGridClassName(className, "products", nextId, postIds),
										});
									},
							  })
							: null,
						mode === "posts"
							? el(PostsSelect, {
									postIds: postIds,
									onChange: function (ids) {
										props.setAttributes({
											className: buildGridClassName(className, "posts", catId, ids),
										});
									},
							  })
							: null,
						hint
							? el(Notice, { status: "info", isDismissible: false }, hint)
							: null
					)
				)
			);
		};
	}, "withBentoContent");

	wp.hooks.addFilter("editor.BlockEdit", "fairflock/bento-span", withBentoSpan);
	wp.hooks.addFilter("editor.BlockEdit", "fairflock/bento-content", withBentoContent);
})(window.wp, window.fairflockBento || {});
