/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";

/**
 * SkillAdapter
 */

class SkillAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Description' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text' }],
      ['description', { label: 'Description', type: 'textarea', validation: '' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Description',
        dataIndex: 'description',
      },
    ];
  }

  showViewButton() {
    return false;
  }

  getHelpTitle() {
    return this.gt('Skills');
  }

  getHelpDescription() {
    return this.gt('Here you can define the different types of skills that you can add under each employee profile.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/job-details-and-qualifications-set-up/';
  }
}


/**
 * EducationAdapter
 */

class EducationAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Description' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text' }],
      ['description', { label: 'Description', type: 'textarea', validation: '' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Description',
        dataIndex: 'description',
      },
    ];
  }

  showViewButton() {
    return false;
  }

  getHelpTitle() {
    return this.gt('Education');
  }

  getHelpDescription() {
    return this.gt('Here you can define the different levels of education that you can add under each employee profile.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/job-details-and-qualifications-set-up/';
  }
}


/**
 * CertificationAdapter
 */

class CertificationAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Description' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text' }],
      ['description', { label: 'Description', type: 'textarea', validation: '' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Description',
        dataIndex: 'description',
      },
    ];
  }

  showViewButton() {
    return false;
  }

  getHelpTitle() {
    return this.gt('Certifications');
  }

  getHelpDescription() {
    return this.gt('Here you can define the different types of certifications that you can add under each employee profile.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/job-details-and-qualifications-set-up/';
  }
}


/**
 * LanguageAdapter
 */

class LanguageAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Description' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text' }],
      ['description', { label: 'Description', type: 'textarea', validation: '' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Description',
        dataIndex: 'description',
      },
    ];
  }

  showViewButton() {
    return false;
  }

}

module.exports = {
  SkillAdapter,
  EducationAdapter,
  CertificationAdapter,
  LanguageAdapter,
};
