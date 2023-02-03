import ReactModalAdapterBase from './ReactModalAdapterBase';

class ReactifiedAdapterBase extends ReactModalAdapterBase {
  getTableColumns() {
    const fields = this.getDataMapping();
    const headers = this.getHeaders();

    return fields.map((field, index) => {
      if (headers[index]) {
        if (headers[index].bVisible === false) {
          return null;
        }
        return {
          title: headers[index].sTitle,
          dataIndex: field,
          sorter: true,
          ...(headers[index].fieldRenderer ? { render: headers[index].fieldRenderer } : {})
        }
      }
      return null;
    }).filter(column => !!column);
  }
}

export default ReactifiedAdapterBase;
