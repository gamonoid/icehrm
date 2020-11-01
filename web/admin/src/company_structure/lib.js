/* eslint-disable prefer-destructuring */
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global d3, nv */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import AdapterBase from '../../../api/AdapterBase';

class CompanyStructureAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'title',
      'address',
      'type',
      'country',
      'timezone',
      'parent',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Address', bSortable: false },
      { sTitle: 'Type' },
      { sTitle: 'Country', sClass: 'center' },
      { sTitle: 'Time Zone' },
      { sTitle: 'Parent Structure' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'title',
        sorter: true,
      },
      {
        title: 'Address',
        dataIndex: 'address',
      },
      {
        title: 'Type',
        dataIndex: 'type',
      },
      {
        title: 'Country',
        dataIndex: 'country',
      },
      {
        title: 'Time Zone',
        dataIndex: 'timezone',
      },
      {
        title: 'Parent Structure',
        dataIndex: 'parent',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['title', { label: 'Name', type: 'text', validation: '' }],
      ['description', { label: 'Details', type: 'textarea', validation: '' }],
      ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
      ['type', { label: 'Type', type: 'select', source: [['Company', 'Company'], ['Head Office', 'Head Office'], ['Regional Office', 'Regional Office'], ['Department', 'Department'], ['Unit', 'Unit'], ['Sub Unit', 'Sub Unit'], ['Other', 'Other']] }],
      ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }],
      ['timezone', {
        label: 'Time Zone', type: 'select2', 'allow-null': false, 'remote-source': ['Timezone', 'name', 'details', 'getTimezonesWithOffset'],
      }],
      ['parent', {
        label: 'Parent Structure', type: 'select', 'allow-null': true, 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['heads', {
        label: 'Heads', type: 'select2multi', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  postRenderForm(object, $tempDomObj) {
    if (object === undefined
            || object === null
            || object.id === null
            || object.id === undefined || object.id === ''
    ) {
      $tempDomObj.find('#field_heads').hide();
    }
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/employees/employee-information-setup';
  }
}


/*
 * Company Graph
 */


class CompanyGraphAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.nodeIdCounter = 0;
  }

  getDataMapping() {
    return [
      'id',
      'title',
      'address',
      'type',
      'country',
      'timezone',
      'parent',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Address', bSortable: false },
      { sTitle: 'Type' },
      { sTitle: 'Country', sClass: 'center' },
      { sTitle: 'Time Zone' },
      { sTitle: 'Parent Structure' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['title', { label: 'Name', type: 'text', validation: '' }],
      ['description', { label: 'Details', type: 'textarea', validation: '' }],
      ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
      ['type', { label: 'Type', type: 'select', source: [['Company', 'Company'], ['Head Office', 'Head Office'], ['Regional Office', 'Regional Office'], ['Department', 'Department'], ['Unit', 'Unit'], ['Sub Unit', 'Sub Unit'], ['Other', 'Other']] }],
      ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }],
      ['timezone', {
        label: 'Time Zone', type: 'select2', 'allow-null': false, 'remote-source': ['Timezone', 'name', 'details', 'getTimezonesWithOffset'],
      }],
      ['parent', {
        label: 'Parent Structure', type: 'select', 'allow-null': true, 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['heads', {
        label: 'Heads', type: 'select2multi', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  convertToTree(data) {
    const ice = {};
    ice.id = -1;
    ice.title = '';
    ice.name = '';
    ice.children = [];

    let parent = null;

    const added = {};


    for (let i = 0; i < data.length; i++) {
      data[i].name = data[i].title;

      if (data[i].parent != null && data[i].parent !== undefined) {
        parent = this.findParent(data, data[i].parent);
        if (parent != null) {
          if (parent.children === undefined || parent.children == null) {
            parent.children = [];
          }
          parent.children.push(data[i]);
        }
      }
    }

    for (let i = 0; i < data.length; i++) {
      if (data[i].parent == null || data[i].parent === undefined) {
        ice.children.push(data[i]);
      }
    }

    return ice;
  }


  findParent(data, parent) {
    for (let i = 0; i < data.length; i++) {
      if (data[i].title === parent || data[i].title === parent) {
        return data[i];
      }
    }
    return null;
  }


  createTable(elementId) {
    $('#tabPageCompanyGraph').html('');
    const that = this;
    const sourceData = this.sourceData;

    // this.fixCyclicParent(sourceData);
    const treeData = this.convertToTree(sourceData);
    const m = [20, 120, 20, 120];
    const w = 5000 - m[1] - m[3];
    const h = 1000 - m[0] - m[2];

    const tree = d3.layout.tree()
      .size([h, w]);

    this.diagonal = d3.svg.diagonal()
      .projection(d => [d.y, d.x]);

    this.vis = d3.select('#tabPageCompanyGraph').append('svg:svg')
      .attr('width', w + m[1] + m[3])
      .attr('height', h + m[0] + m[2])
      .append('svg:g')
      .attr('transform', `translate(${m[3]},${m[0]})`);

    const root = treeData;
    root.x0 = h / 2;
    root.y0 = 0;

    function toggleAll(d) {
      if (d.children) {
        console.log(d.name);
        d.children.forEach(toggleAll);
        that.toggle(d);
      }
    }
    this.update(root, tree, root);
  }

  update(source, tree, root) {
    const that = this;
    const duration = d3.event && d3.event.altKey ? 5000 : 500;

    // Compute the new tree layout.
    const nodes = tree.nodes(root).reverse();

    // Normalize for fixed-depth.
    nodes.forEach((d) => { d.y = d.depth * 180; });

    // Update the nodes�
    const node = that.vis.selectAll('g.node')
      .data(nodes, d => d.id || (d.id = ++that.nodeIdCounter));

    // Enter any new nodes at the parent's previous position.
    const nodeEnter = node.enter().append('svg:g')
      .attr('class', 'node')
      .attr('transform', d => `translate(${source.y0},${source.x0})`)
      .on('click', (d) => { that.toggle(d); that.update(d, tree, root); });

    nodeEnter.append('svg:circle')
      .attr('r', 1e-6)
      .style('fill', d => (d._children ? 'lightsteelblue' : '#fff'));

    nodeEnter.append('svg:text')
      .attr('x', d => (d.children || d._children ? -10 : 10))
      .attr('dy', '.35em')
      .attr('text-anchor', d => (d.children || d._children ? 'end' : 'start'))
      .text(d => d.name)
      .style('fill-opacity', 1e-6);

    // Transition nodes to their new position.
    const nodeUpdate = node.transition()
      .duration(duration)
      .attr('transform', d => `translate(${d.y},${d.x})`);

    nodeUpdate.select('circle')
      .attr('r', 4.5)
      .style('fill', d => (d._children ? 'lightsteelblue' : '#fff'));

    nodeUpdate.select('text')
      .style('fill-opacity', 1);

    // Transition exiting nodes to the parent's new position.
    const nodeExit = node.exit().transition()
      .duration(duration)
      .attr('transform', d => `translate(${source.y},${source.x})`)
      .remove();

    nodeExit.select('circle')
      .attr('r', 1e-6);

    nodeExit.select('text')
      .style('fill-opacity', 1e-6);

    // Update the links�
    const link = that.vis.selectAll('path.link')
      .data(tree.links(nodes), d => d.target.id);

    // Enter any new links at the parent's previous position.
    link.enter().insert('svg:path', 'g')
      .attr('class', 'link')
      .attr('d', (d) => {
        const o = { x: source.x0, y: source.y0 };
        return that.diagonal({ source: o, target: o });
      })
      .transition()
      .duration(duration)
      .attr('d', that.diagonal);

    // Transition links to their new position.
    link.transition()
      .duration(duration)
      .attr('d', that.diagonal);

    // Transition exiting nodes to the parent's new position.
    link.exit().transition()
      .duration(duration)
      .attr('d', (d) => {
        const o = { x: source.x, y: source.y };
        return that.diagonal({ source: o, target: o });
      })
      .remove();

    // Stash the old positions for transition.
    nodes.forEach((d) => {
      d.x0 = d.x;
      d.y0 = d.y;
    });
  }

  // Toggle children.
  toggle(d) {
    if (d.children) {
      d._children = d.children;
      d.children = null;
    } else {
      d.children = d._children;
      d._children = null;
    }
  }


  getSourceDataById(id) {
    for (let i = 0; i < this.sourceData.length; i++) {
      if (this.sourceData[i].id == id) {
        return this.sourceData[i];
      }
    }

    return null;
  }

  fixCyclicParent(sourceData) {
    let errorMsg = '';
    for (let i = 0; i < sourceData.length; i++) {
      const obj = sourceData[i];


      let curObj = obj;
      const parentIdArr = {};
      parentIdArr[curObj.id] = 1;

      while (curObj.parent != null && curObj.parent != undefined) {
        const parent = this.getSourceDataById(curObj.parent);
        if (parent == null) {
          break;
        } else if (parentIdArr[parent.id] == 1) {
          errorMsg = `${obj.title}'s parent structure set to ${parent.title}<br/>`;
          obj.parent = null;
          break;
        }
        parentIdArr[parent.id] = 1;
        curObj = parent;
      }
    }

    if (errorMsg !== '') {
      this.showMessage('Company Structure is having a cyclic dependency', `We found a cyclic dependency due to following reasons:<br/>${errorMsg}`);
      return false;
    }

    return true;
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/employees/employee-information-setup';
  }
}

module.exports = { CompanyStructureAdapter, CompanyGraphAdapter };
