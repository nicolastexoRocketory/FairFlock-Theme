(function () {
	"use strict";

	document.addEventListener("submit", function (event) {
		var form = event.target;
		if (!(form instanceof HTMLFormElement) || !form.hasAttribute("data-ff-ptable-delete")) {
			return;
		}
		if (
			!window.confirm(
				"Diese Vorlage löschen? Produkte mit dieser Vorlage zeigen danach ihre eigene Tabelle."
			)
		) {
			event.preventDefault();
		}
	});

	var root = document.querySelector("[data-ff-ptable-admin]");
	if (!root) {
		return;
	}

	var columnsEl = root.querySelector("[data-ff-ptable-columns]");
	var rowsEl = root.querySelector("[data-ff-ptable-rows]");
	var maxColumns = parseInt(root.getAttribute("data-max-columns"), 10) || 16;
	var maxRows = parseInt(root.getAttribute("data-max-rows"), 10) || 24;

	function columnFields() {
		return columnsEl ? columnsEl.querySelectorAll("[data-ff-ptable-column]") : [];
	}

	function rowFields() {
		return rowsEl ? rowsEl.querySelectorAll("[data-ff-ptable-row]") : [];
	}

	function actionsCell(row) {
		return row.querySelector(".ff-ptable-admin__actions");
	}

	function reindex() {
		var rows = rowFields();
		Array.prototype.forEach.call(rows, function (row, rowIndex) {
			var inputs = row.querySelectorAll("td:not(.ff-ptable-admin__actions) input");
			Array.prototype.forEach.call(inputs, function (input, colIndex) {
				input.name = "ff_ptable_rows[" + rowIndex + "][" + colIndex + "]";
				input.setAttribute("aria-label", "Zeile " + (rowIndex + 1) + ", Spalte " + (colIndex + 1));
			});
		});
		var cols = columnFields();
		Array.prototype.forEach.call(cols, function (col, colIndex) {
			var input = col.querySelector("input");
			if (input) {
				input.setAttribute("aria-label", "Spalte " + (colIndex + 1));
			}
		});
		var empty = root.querySelector("[data-ff-ptable-empty]");
		if (empty) {
			empty.hidden = rows.length > 0;
		}
		var addCol = root.querySelector("[data-ff-ptable-add-col]");
		if (addCol) {
			addCol.disabled = cols.length >= maxColumns;
		}
	}

	function columnHead(placeholder) {
		var wrap = document.createElement("th");
		wrap.className = "ff-ptable-admin__col";
		wrap.setAttribute("data-ff-ptable-column", "");
		wrap.scope = "col";
		wrap.innerHTML =
			'<div class="ff-ptable-admin__head">' +
			'<input type="text" name="ff_ptable_columns[]" value="" maxlength="180" placeholder="' +
			placeholder +
			'" />' +
			'<button type="button" class="ff-ptable-admin__icon" data-ff-ptable-remove-col aria-label="Spalte entfernen">&times;</button>' +
			"</div>";
		return wrap;
	}

	function addColumn() {
		if (!columnsEl || columnFields().length >= maxColumns) {
			return;
		}
		var head = columnHead("Spaltenname");
		var actionsHead = columnsEl.querySelector(".ff-ptable-admin__actions");
		columnsEl.insertBefore(head, actionsHead);
		Array.prototype.forEach.call(rowFields(), function (row) {
			var cell = document.createElement("td");
			var input = document.createElement("input");
			input.type = "text";
			input.maxLength = 180;
			cell.appendChild(input);
			row.insertBefore(cell, actionsCell(row));
		});
		reindex();
		var input = head.querySelector("input");
		if (input) {
			input.focus();
		}
	}

	function addRow() {
		if (!rowsEl || rowFields().length >= maxRows) {
			return;
		}
		var count = Math.max(1, columnFields().length);
		var row = document.createElement("tr");
		row.setAttribute("data-ff-ptable-row", "");
		for (var i = 0; i < count; i++) {
			var cell = document.createElement("td");
			var input = document.createElement("input");
			input.type = "text";
			input.maxLength = 180;
			cell.appendChild(input);
			row.appendChild(cell);
		}
		var actions = document.createElement("td");
		actions.className = "ff-ptable-admin__actions";
		var remove = document.createElement("button");
		remove.type = "button";
		remove.className = "ff-ptable-admin__icon";
		remove.setAttribute("data-ff-ptable-remove-row", "");
		remove.setAttribute("aria-label", "Zeile entfernen");
		remove.textContent = "×";
		actions.appendChild(remove);
		row.appendChild(actions);
		rowsEl.appendChild(row);
		reindex();
		var first = row.querySelector("input");
		if (first) {
			first.focus();
		}
	}

	function removeColumn(button) {
		var cols = columnFields();
		if (cols.length <= 1) {
			return;
		}
		var wrap = button.closest("[data-ff-ptable-column]");
		if (!wrap) {
			return;
		}
		var index = Array.prototype.indexOf.call(cols, wrap);
		wrap.remove();
		Array.prototype.forEach.call(rowFields(), function (row) {
			var cells = row.querySelectorAll("td:not(.ff-ptable-admin__actions)");
			if (cells[index]) {
				cells[index].remove();
			}
		});
		reindex();
	}

	function removeRow(button) {
		var row = button.closest("[data-ff-ptable-row]");
		if (row) {
			row.remove();
			reindex();
		}
	}

	root.addEventListener("click", function (event) {
		var target = event.target;
		if (!(target instanceof Element)) {
			return;
		}
		if (target.closest("[data-ff-ptable-add-col]")) {
			event.preventDefault();
			addColumn();
		} else if (target.closest("[data-ff-ptable-add-row]")) {
			event.preventDefault();
			addRow();
		} else if (target.closest("[data-ff-ptable-remove-col]")) {
			event.preventDefault();
			removeColumn(target.closest("[data-ff-ptable-remove-col]"));
		} else if (target.closest("[data-ff-ptable-remove-row]")) {
			event.preventDefault();
			removeRow(target.closest("[data-ff-ptable-remove-row]"));
		}
	});

	function syncTemplate() {
		var select = root.querySelector("[data-ff-ptable-template]");
		if (!select) {
			return;
		}
		var id = select.value;
		root.classList.toggle("is-from-template", id !== "");
		var previews = root.querySelectorAll("[data-ff-ptable-preview]");
		Array.prototype.forEach.call(previews, function (preview) {
			preview.hidden = preview.getAttribute("data-ff-ptable-preview") !== id;
		});
	}

	var templateSelect = root.querySelector("[data-ff-ptable-template]");
	if (templateSelect) {
		templateSelect.addEventListener("change", syncTemplate);
		syncTemplate();
	}
})();
