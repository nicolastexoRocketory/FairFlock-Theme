/**
 * FairFlock contact form block — editor registration.
 */
(function (wp) {
	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.serverSideRender) {
		return;
	}

	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var ExternalLink = wp.components.ExternalLink;
	var ServerSideRender = wp.serverSideRender;

	registerBlockType("fairflock/contact-form", {
		edit: function () {
			var blockProps = useBlockProps({ className: "ff-form-block-editor" });
			var adminUrl =
				(typeof fairflockFormEditor !== "undefined" && fairflockFormEditor.adminUrl) ||
				"/wp-admin/admin.php?page=fairflock-formular";

			return el(
				"div",
				blockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: "Kontaktformular", initialOpen: true },
						el(
							"p",
							{ style: { marginTop: 0 } },
							"Felder, Texte, SMTP und Spam-Schutz stellst du unter FairFlock → Formular ein."
						),
						el(ExternalLink, { href: adminUrl }, "Formular-Einstellungen öffnen")
					)
				),
				el(ServerSideRender, {
					block: "fairflock/contact-form",
					EmptyResponsePlaceholder: function () {
						return el(
							"p",
							{ className: "ff-form-block-placeholder" },
							"Kontaktformular (Vorschau lädt …)"
						);
					},
				})
			);
		},
		save: function () {
			return null;
		},
	});
})(window.wp);
