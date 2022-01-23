
function stringCompare (str1, str2) {
  return str1 < str2 ? -1 : str1 > str2;
}

function disableTableRow (tr, classList) {
  for (var i=1; i<tr.childNodes.length; i++) {
    var td = tr.childNodes[i].childNodes[0];
    td.readOnly = true;
    td.classList = classList;
  };
  tr.disabled = true;
}

function enableTableRow (tr, classList) {
  for (var i=1; i<tr.childNodes.length; i++) {
    var td = tr.childNodes[i].childNodes[0];
    td.readOnly = false;
    td.classList = classList;
  };
  tr.disabled = false;
}

function createAttachedElement (elType, parentNode, classList) {
  var element = document.createElement (elType);
  if (classList != null) {
    element.classList = classList;
  }
  if (parentNode != null) {
    parentNode.appendChild (element);
  }
  return element;
}

class LiveTable {
  constructor (dataFunc, recUpdateFunc, recRemoveFunc) {
    this.parentNodeId = null;
    this.parentNode = null;
    this.element = null;

    this.divClassList = null;
    this.tableClassList = null;
    this.theadClassList = null;
    this.tbodyClassList = null;
    this.trEvenClassList = null;
    this.trOddClassList = null;
    this.thClassList = null;
    this.tdClassList = null;
    this.sortBtnClassList = null;
    this.pageCtlClassList = null;
    this.pageCtlBtnClassList = null;
    this.checkboxClassList = null;
    this.inputClassList = null;
    this.editableRowClassList = null;
    this.uneditableRowClassList = null;
    this.changedRowClassList = null;

    this.dataFunc = dataFunc;
    this.recUpdateFunc = recUpdateFunc;
    this.recRemoveFunc = recRemoveFunc;
    this.sortFuncs = new Object ();
    this.data = null;
    this.dataHeaders = null;
    this.columnsSortDirections = [];

    this.nchecks = 0;
  }

  setSortFunc (colNumber, func) {
    this.sortFuncs[colNumber] = func;
  }

  sortColumn (colNumber, direction) {
    var sortFunc = this.sortFuncs[colNumber];
    if (sortFunc == undefined) {
      sortFunc = (lhs, rhs) => {
        lhs = lhs + '';
        rhs = rhs + '';
        return stringCompare (lhs, rhs);
      }
    }
    var rowSortFunc = (lhs, rhs) => {
        return sortFunc (lhs[colNumber], rhs[colNumber]);
    }
    this.data.table.sort (rowSortFunc);

    if (direction === 1)
      this.data.table.reverse ();

    this.render ();
  }

  saveChanges () {
    this.element.childNodes[1].childNodes[1].childNodes.forEach ((row) => {
      if (row.changed) {
        var values = [];
        for (var i=1; i<row.childNodes.length; i++) {
          values.push (row.childNodes[i].firstChild.value);
        }
        this.recUpdateFunc (values);
        row.changed = false;
      }
    });
  }

  removeRecords () {
    this.element.childNodes[1].childNodes[1].childNodes.forEach ((row) => {
      if (row.firstChild.checked) {
        var values = [];
        for (var i=1; i<row.childNodes.length; i++) {
          values.push (row.childNodes[i].firstChild.value);
        }
        this.recRemoveFunc (values);
        row.changed = false;
      }
    });
  }

  createPageCtl (parentNode) {
    var div = createAttachedElement ("div", parentNode, this.pageCtlClassList);
    var btnRefresh = createAttachedElement ("button", div, this.pageCtlBtnClassList);
    var btnDelete = createAttachedElement ("button", div, this.pageCtlBtnClassList);
    var btnSaveChanges = createAttachedElement ("button", div, this.pageCtlBtnClassList);

    btnRefresh.innerHTML = "Refresh";
    btnRefresh.onclick = () => {
      this.render ();
    }
    btnDelete.innerHTML = "Delete";
    btnDelete.disabled = true;
    btnDelete.onclick = () => {
      // TODO: Complete this by calling this.removeRecords
      console.log ("Removing all checked records");
    }

    btnSaveChanges.innerHTML = "Save Changes";
    btnSaveChanges.disabled = true;
    btnSaveChanges.onclick = () => {
      var topSaveBtn = parentNode.firstChild.lastChild;
      var bottomSaveBtn = parentNode.lastChild.lastChild;
      this.saveChanges ();
      topSaveBtn.disabled = true;
      bottomSaveBtn.disabled = true;
    }

    return div;
  }

  createRowCheckbox (parentNode, rowNumber) {
    var cb = createAttachedElement ("input", parentNode, this.checkboxClassList);
    cb.type = "checkbox";
    cb.rowNumber = rowNumber;
    if (rowNumber < 0) {
      cb.onclick = () => {
        var checkedValue = cb.checked;
        this.element.childNodes[1].childNodes[1].childNodes.forEach ((row) => {
          row.firstChild.checked = checkedValue;
        });
      }
    } else {
      cb.onclick = () => {
        if (cb.checked == true) {
          this.nchecks++;
        } else {
          this.nchecks--;
        }
        var deleteBtn = this.element.childNodes[0].childNodes[1];
        if (this.nchecks > 0) {
          deleteBtn.disabled = false;
        } else {
          deleteBtn.disabled = true;
        }
      }
    }
    return cb;
  }

  createColumnHeader (colNumber, text, parentNode) {
    var element = createAttachedElement ("td", parentNode, "");
    var btn = createAttachedElement ("button", element, this.sortBtnClassList);
    if (this.columnsSortDirections[colNumber] == undefined) {
      this.columnsSortDirections[colNumber] = 0;
    }

    btn.onclick = (evt) => {
      this.sortColumn (colNumber, this.columnsSortDirections[colNumber]++ % 2);
      btn.innerHTML = "clicked";
    }
    btn.innerHTML = text;
    return element;
  }

  createTableElement () {
    var div = createAttachedElement ("div", null, this.divClassList);
    var topPageCtl = this.createPageCtl (div);
    var element = createAttachedElement ("table", div, this.tableClassList);
    var thead = createAttachedElement ("thead", element, this.theadClassList);
    var trhead = createAttachedElement ("tr", thead, "");
    var tbody = createAttachedElement ("tbody", element, this.tbodyClassList);

    this.createRowCheckbox (trhead, -1);
    for (var i=0; i<this.dataHeaders.length; i++) {
      var colName = this.createColumnHeader (i, this.dataHeaders[i], trhead);
      trhead.appendChild (colName);
    }
    createAttachedElement ("td", trhead, null);

    var ncols = this.data.table[0].length;
    for (var i=0; i<this.data.table.length; i++) {
      var trClassList = (i % 2) == 1 ? this.trOddClassList : this.trEvenClassList;
      var tr = createAttachedElement ("tr", tbody, trClassList);
      tr.changed = false;
      this.createRowCheckbox (tr, i);
      // TODO: Must have onclick() handler that checks all checkboxes and
      // enables the delete button if any checkbox is checked.
      for (var j=0; j<ncols; j++) {
        var td = createAttachedElement ("td", tr, this.tdClassList);
        var input = createAttachedElement ("input", td, null);
        input.value = this.data.table[i][j];
        input.classList = this.inputClassList;
        input.size = input.value.length;
        input.onchange = function () {
          var localTr = this.parentNode.parentNode;
          var topSaveBtn = localTr.parentNode.parentNode.parentNode.firstChild.lastChild;
          var bottomSaveBtn = localTr.parentNode.parentNode.parentNode.lastChild.lastChild;
          topSaveBtn.disabled =  false;
          bottomSaveBtn.disabled =  false;
          localTr.changed = true;
          localTr.classList.add (localTr.changedRowClassList);
        }
        input.onblur = function () {
          var localTr = this.parentNode.parentNode;
          disableTableRow (localTr, localTr.uneditableRowClassList);
        }
      }
      disableTableRow (tr, this.uneditableRowClassList);
      tr.editableRowClassList = this.editableRowClassList;
      tr.uneditableRowClassList = this.uneditableRowClassList;
      tr.changedRowClassList = this.changedRowClassList;
      tr.onkeydown = function (evt) {
        if (evt.key === 'Escape') {
          disableTableRow (this, this.uneditableRowClassList);
        }
        for (var i=1; i<this.childNodes.length; i++) {
          var input = this.childNodes[i].childNodes[0];
          input.size = input.value.length;
        }
      }

      tr.onclick = function () {
        var eClass = this.editableRowClassList;
        var ueClass = this.uneditableRowClassList;
        console.log (`${eClass} : ${ueClass}`);
        if (this.disabled) {
          tbody.childNodes.forEach ((tr) => {
            disableTableRow (tr, ueClass);
          });
          enableTableRow (this, eClass);
        }
      };
      tbody.appendChild (tr);
    }
    var bottomPageCtl = this.createPageCtl (div);
    return div;
  }

  render () {
    if (this.data == null) {
      this.data = this.dataFunc ({
        "Page":       this.currentPage,
        "PageSize":   this.pageSize
      });
      this.dataHeaders = this.data.table.shift ();
    }

    if (this.parentNode) {
      this.parentNode.removeChild (this.element);
      this.parentNode = null;
    }

    if (this.element != null) {
      this.element = null;
    }

    this.element = this.createTableElement ();

    this.parentNode = document.getElementById (this.parentNodeId);
    this.parentNode.appendChild (this.element);
  }
}
