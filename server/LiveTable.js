
class LiveTable {
  constructor (dataFunc, recUpdateFunc, recRemoveFunc) {
    this.parentNode = null;
    this.element = null;
    this.classList = "";

    this.dataFunc = dataFunc;
    this.data = null;
  }

  createTable () {
    var element = document.createElement ("table");
    element.border = "line";
    var rowNum = 0;
    this.data.table.forEach ((row) => {
      var rowElement = document.createElement ("tr");
      row.forEach ((col) => {
        var colElement = document.createElement (rowNum == 0 ? "th" : "td");
        colElement.innerHTML = col;
        rowElement.appendChild (colElement);
      });
      element.appendChild (rowElement);
      rowNum++;
    });
    return element;
  }

  render (parentNodeId) {
    if (this.data == null) {
      this.data = this.dataFunc ({
        "Page":     this.current_page,
        "PageSize": this.page_size
      });
    }

    if (this.element == null) {
      this.element = this.createTable ();
    }

    if (this.parentNode) {
      this.parentNode.removeChild (this.element);
      this.parentNode = null;
    }

    this.parentNode = document.getElementById (parentNodeId);
    this.parentNode.appendChild (this.element);
  }
}
