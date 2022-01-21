
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

function createColumnHeader (text, parentNode) {
  var ret = createAttachedElement ("td", parentNode, "");
  ret.innerHTML = text;
  return ret;
}

class LiveTable {
  constructor (dataFunc, recUpdateFunc, recRemoveFunc) {
    this.parentNode = null;
    this.element = null;
    this.tableClassList = null;
    this.theadClassList = null;
    this.tbodyClassList = null;
    this.trEvenClassList = null;
    this.trOddClassList = null;
    this.thClassList = null;
    this.tdClassList = null;

    this.dataFunc = dataFunc;
    this.data = null;
  }

  createTableElement () {
    var element = createAttachedElement ("table", null, this.tableClassList);
    var thead = createAttachedElement ("thead", element, this.theadClassList);
    var trhead = createAttachedElement ("tr", thead, "");
    var tbody = createAttachedElement ("tbody", element, this.tbodyClassList);

    for (var i=0; i<this.data.table[0].length; i++) {
      var colName = createColumnHeader (this.data.table[0][i], trhead);
      trhead.appendChild (colName);
    }

    var ncols = this.data.table[0].length;
    for (var i=1; i<this.data.table.length; i++) {
      var trClassList = (i % 2) == 1 ? this.trOddClassList : this.trEvenClassList;
      var tr = createAttachedElement ("tr", tbody, trClassList);
      console.log (i + " : Created tr element " + ncols);
      for (var j=0; j<ncols; j++) {
        console.log ("Creating td element");
        var td = createAttachedElement ("td", tr, this.tdClassList);
        td.innerHTML = this.data.table[i][j];
      }
      tbody.appendChild (tr);
    }
    return element;
  }

  render (parentNodeId) {
    if (this.data == null) {
      this.data = this.dataFunc ({
        "Page":       this.current_page,
        "PageSize":   this.page_size
      });
    }

    if (this.element == null) {
      this.element = this.createTableElement ();
    }

    if (this.parentNode) {
      this.parentNode.removeChild (this.element);
      this.parentNode = null;
    }

    this.parentNode = document.getElementById (parentNodeId);
    this.parentNode.appendChild (this.element);
  }
}
