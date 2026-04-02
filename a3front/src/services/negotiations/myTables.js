class DataTableCustomize {
  element;
  headers;
  items;
  copyItems;
  selected;
  pagination;

  constructor(selector) {
    this.element = document.querySelector(selector);
    console.log(this.element);

    this.headers = [];
    this.items = [];
    this.pagination = {
      total: 0,
      noItemsPerPage: 0,
      actual: 0,
      pointer: 0,
      diff: 0,
      lastPageBeforeDots: 0,
      noButtonsBeforeBots: 4,
    };

    this.selected = [];
  }

  //vamos a mapear nuestra tabla para extraer los datos
  //y tenerlos en js
  parse() {
    const headers = [...this.element.querySelector('thead tr').children];

    headers.forEach((element) => {
      this.headers.push(element.textContent);
    });

    console.log(this.headers);
  }
}

export default DataTableCustomize;
