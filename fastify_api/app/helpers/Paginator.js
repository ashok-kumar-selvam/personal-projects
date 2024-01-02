class Paginator {
  constructor(items = [], itemsPerPage = 10) {
    this.items = items;
    this.itemsPerPage = itemsPerPage;
    this.totalPages = Math.ceil(items.length / itemsPerPage);
  }

  paginate(pageNumber) {
    const startIndex = (pageNumber - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    const paginatedItems = this.items.slice(startIndex, endIndex);
    return paginatedItems;
  }

  hasPrevious(pageNumber) {
    return pageNumber > 1;
  }

  hasNext(pageNumber) {
    return pageNumber < this.totalPages;
  }
}

module.exports = Paginator;