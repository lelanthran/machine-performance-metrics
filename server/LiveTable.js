
class LiveTable {
  constructor () {
    this.parentNode = null;
    this.element = null;
    this.classList = "";
  }

  populate (matrix) {
    this.element = document.createElement ("table");
    this.element.border = "line";
    var rowNum = 0;
    matrix.forEach ((row) => {
      var rowElement = document.createElement ("tr");
      row.forEach ((col) => {
        var colElement = document.createElement (rowNum == 0 ? "th" : "td");
        colElement.innerHTML = col;
        rowElement.appendChild (colElement);
      });
      this.element.appendChild (rowElement);
      rowNum++;
    });
  }

  render (parentNodeId) {
    if (this.parentNode) {
      this.parentNode.removeChild (this.element);
      this.parentNode = null;
    }
    this.parentNode = document.getElementById (parentNodeId);
    this.parentNode.appendChild (this.element);
  }

}
