
/* *************************************************************
 * TODO:
 *
 *  Add a field controlling whether the first column is an ID. If
 *  it is it must not be editable.
 *
 *  Add a field controlling whether or not a editPopup is allowed.
 *  If it is, then the first column *MUST BE* an ID.
 *
 *  Add in paging controls - firstPage, prevPage, nextPage and
 *  lastPage.
 *
 *
 */
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

    this.divClassList = 'default_divClass';
    this.tableClassList = 'default_tableClass';
    this.theadClassList = 'default_threadClass';
    this.tbodyClassList = 'default_tbodyClass';
    this.trEvenClassList = 'default_trEvenClass';
    this.trOddClassList = 'default_trOddClass';
    this.thClassList = 'default_thClass';
    this.tdClassList = 'default_tdClass';
    this.sortBtnClassList = 'default_sortBtnClass';
    this.pageCtlClassList = 'default_pageCtlClass';
    this.pageCtlRefreshBtnClassList = 'default_pageCtlRefreshBtnClass';
    this.pageCtlSaveBtnClassList = 'default_pageCtlSaveBtnClass';
    this.pageCtlDeleteBtnClassList = 'default_pageCtlDeleteBtnClass';
    this.pageCtlDisabledBtnClassList = 'default_pageCtlDisabledBtnClass';
    this.checkboxClassList = 'default_checkboxClass';
    this.inputClassList = 'default_inputClass';
    this.editableRowClassList = 'default_editableRowClass';
    this.uneditableRowClassList = 'default_uneditableRowClass';
    this.changedRowClassList = 'default_changedRowClass';

    this.dataFunc = dataFunc;
    this.recUpdateFunc = recUpdateFunc;
    this.recRemoveFunc = recRemoveFunc;
    this.sortFuncs = new Object ();
    this.data = null;
    this.dataHeaders = null;
    this.columnsSortDirections = [];

    this.nchecks = 0;
  }

  getSaveButtons () {
    var btns = [];
    btns.push (this.element.firstChild.childNodes[1]);
    btns.push (this.element.lastChild.childNodes[1]);
    return btns;
  }

  getDeleteButtons () {
    var btns = [];
    btns.push (this.element.firstChild.lastChild);
    btns.push (this.element.lastChild.lastChild);
    return btns;
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
      row.classList.remove (this.changedRowClassList);
    });
  }

  removeCheckedRecords () {
    if (this.getSaveButtons()[0].disabled == false) {
      alert ("Cannot delete any records until all changes are saved or discarded");
      return;
    }

    this.element.childNodes[1].childNodes[1].childNodes.forEach ((row) => {
      if (row.firstChild.checked) {
        var values = [];
        for (var i=1; i<row.childNodes.length; i++) {
          values.push (row.childNodes[i].firstChild.value);
        }
        this.recRemoveFunc (values);
      }
    });
    this.render ();
  }

  createPageCtl (parentNode) {
    var div = createAttachedElement ("div", parentNode, this.pageCtlClassList);
    var btnRefresh = createAttachedElement ("button", div, this.pageCtlRefreshBtnClassList);
    var btnSaveChanges = createAttachedElement ("button", div, this.pageCtlDisabledBtnClassList);
    var spacer = createAttachedElement ("span", div, null);
    var btnDelete = createAttachedElement ("button", div, this.pageCtlDisabledBtnClassList);

    btnRefresh.innerHTML = "Refresh";
    btnRefresh.onclick = () => {
      this.render ();
    }

    btnSaveChanges.innerHTML = "Save Changes";
    btnSaveChanges.disabled = true;
    btnSaveChanges.onclick = () => {
      var topSaveBtn = this.getSaveButtons()[0];
      var bottomSaveBtn = this.getSaveButtons()[1];
      this.saveChanges ();
      topSaveBtn.disabled = true;
      topSaveBtn.classList = this.pageCtlDisabledBtnClassList;
      bottomSaveBtn.disabled = true;
      bottomSaveBtn.classList = this.pageCtlDisabledBtnClassList;
    }

    btnDelete.innerHTML = "Delete";
    btnDelete.disabled = true;
    btnDelete.onclick = () => {
      var topDeleteBtn = this.getDeleteButtons()[0];
      var bottomDeleteBtn = this.getDeleteButtons()[1];
      topDeleteBtn.disabled = false;
      bottomDeleteBtn.disabled = false;
      this.removeCheckedRecords ();
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
        var topDeleteBtn = this.getDeleteButtons()[0];
        var bottomDeleteBtn = this.getDeleteButtons()[1];
        if (checkedValue) {
          topDeleteBtn.disabled = false;
          bottomDeleteBtn.disabled = false;
          topDeleteBtn.classList = this.pageCtlDeleteBtnClassList;
          bottomDeleteBtn.classList = this.pageCtlDeleteBtnClassList;
          this.nchecks = this.element.childNodes[1].childNodes[1].childNodes.length;
        } else {
          topDeleteBtn.disabled = true;
          bottomDeleteBtn.disabled = true;
          topDeleteBtn.classList = this.pageCtlDisabledBtnClassList;
          bottomDeleteBtn.classList = this.pageCtlDisabledBtnClassList;
          this.nchecks = 0;
        }
      }
    } else {
      cb.onclick = () => {
        if (cb.checked == true) {
          this.nchecks++;
        } else {
          this.nchecks--;
        }
        var topDeleteBtn = this.getDeleteButtons()[0];
        var bottomDeleteBtn = this.getDeleteButtons()[1];
        if (this.nchecks > 0) {
          topDeleteBtn.disabled = false;
          bottomDeleteBtn.disabled = false;
          topDeleteBtn.classList = this.pageCtlDeleteBtnClassList;
          bottomDeleteBtn.classList = this.pageCtlDeleteBtnClassList;
        } else {
          topDeleteBtn.disabled = true;
          bottomDeleteBtn.disabled = false;
          topDeleteBtn.classList = this.pageCtlDisabledBtnClassList;
          bottomDeleteBtn.classList = this.pageCtlDisabledBtnClassList;
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
      for (var j=0; j<ncols; j++) {
        var td = createAttachedElement ("td", tr, this.tdClassList);
        var input = createAttachedElement ("input", td, null);
        input.value = this.data.table[i][j];
        input.classList = this.inputClassList;
        var slen = input.value.length;
        if (slen > 0) {
          input.size = slen;
        }
        input.onchange = function () {
          var localTr = this.parentNode.parentNode;
          var topSaveBtn = localTr.parentNode.parentNode.parentNode.firstChild.childNodes[1];
          var bottomSaveBtn = localTr.parentNode.parentNode.parentNode.lastChild.childNodes[1];
          topSaveBtn.disabled =  false;
          bottomSaveBtn.disabled =  false;
          topSaveBtn.classList = localTr.obj.pageCtlSaveBtnClassList;
          bottomSaveBtn.classList = localTr.obj.pageCtlSaveBtnClassList;
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
      tr.obj = this;
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

  async render () {
    if (this.data == null) {
      this.data = await this.dataFunc ({
        "Page":       this.currentPage,
        "PageSize":   this.pageSize
      });
      if (this.data == null) {
        return;
      }
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
