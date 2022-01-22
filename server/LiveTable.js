
function stringCompare (str1, str2) {
  return str1 < str2 ? -1 : str1 > str2;
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
    var element = createAttachedElement ("table", null, this.tableClassList);
    var thead = createAttachedElement ("thead", element, this.theadClassList);
    var trhead = createAttachedElement ("tr", thead, "");
    var tbody = createAttachedElement ("tbody", element, this.tbodyClassList);

    // TODO: Append table paging controls here: div.appendChild (tblctl);

    // TODO: Append empty cell here (header cell in this column is empty, data
    // cells have the edit/delete/checkbox elements).
    for (var i=0; i<this.dataHeaders.length; i++) {
      var colName = this.createColumnHeader (i, this.dataHeaders[i], trhead);
      trhead.appendChild (colName);
    }
    // TODO: Append empty cell here (header cell in this column is empty, data
    // cells have the edit/delete/checkbox elements).

    var ncols = this.data.table[0].length;
    for (var i=0; i<this.data.table.length; i++) {
      var trClassList = (i % 2) == 1 ? this.trOddClassList : this.trEvenClassList;
      var tr = createAttachedElement ("tr", tbody, trClassList);
      // TODO: Add in the edit/delete/checkbox elements here
      for (var j=0; j<ncols; j++) {
        var td = createAttachedElement ("td", tr, this.tdClassList);
        td.innerHTML = this.data.table[i][j];
      }
      // TODO: Add in the edit/delete/checkbox elements here
      tbody.appendChild (tr);
    }
    // TODO: Append table paging controls here: div.appendChild (tblctl);
    return element;
  }

  render () {
    if (this.data == null) {
      this.data = this.dataFunc ({
        "Page":       this.current_page,
        "PageSize":   this.page_size
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
