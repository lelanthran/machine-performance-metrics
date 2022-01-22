
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
    this.checkboxClassList = null;
    this.inputClassList = null;
    this.editableRowClassList = null;
    this.uneditableRowClassList = null;

    this.dataFunc = dataFunc;
    this.sortFuncs = new Object ();
    this.data = null;
    this.dataHeaders = null;
    this.columnsSortDirections = [];
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

  createPageCtl (parentNode) {
    // TODO: Complete this.
    var tmp = createAttachedElement ("i", parentNode, this.pageCtlClassList);
    tmp.innerHTML = "Paging control";
    return tmp;
  }

  createRowCheckbox (parentNode, rowNumber) {
    var tmp = createAttachedElement ("input", parentNode, this.checkboxClassList);
    tmp.type = "checkbox";
    tmp.rowNumber = rowNumber;
    if (rowNumber < 0) {
      tmp.onclick = () => {
        var checkedValue = tmp.checked;
        this.element.childNodes[1].childNodes[1].childNodes.forEach ((row) => {
          row.firstChild.checked = checkedValue;
        });
      }
    }
    return tmp;
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
      this.createRowCheckbox (tr, i);
      for (var j=0; j<ncols; j++) {
        var td = createAttachedElement ("td", tr, this.tdClassList);
        var input = createAttachedElement ("input", td, null);
        input.value = this.data.table[i][j];
        input.classList = this.inputClassList;
        input.size = input.value.length;
        input.onchange = function () {
          var localTr = this.parentNode.parentNode;
          localTr.changed = true;
          console.log (localTr);
        }
        input.onblur = function () {
          var localTr = this.parentNode.parentNode;
          disableTableRow (localTr, localTr.uneditableRowClassList);
        }
      }
      disableTableRow (tr, this.uneditableRowClassList);
      tr.editableRowClassList = this.editableRowClassList;
      tr.uneditableRowClassList = this.uneditableRowClassList;
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
