import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

class EmployeeSkillAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'skill_id',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Skill' },
      { sTitle: 'Details' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Skill',
        dataIndex: 'skill_id',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['skill_id', {
        label: 'Skill', type: 'select2', 'allow-null': true, 'remote-source': ['Skill', 'id', 'name'],
      }],
      ['details', { label: 'Details', type: 'textarea', validation: '' }],
    ];
  }
}

/**
 * EmployeeEducationAdapter
 */

class EmployeeEducationAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'education_id',
      'institute',
      'date_start',
      'date_end',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Qualification' },
      { sTitle: 'Institute' },
      { sTitle: 'Start Date' },
      { sTitle: 'Completed On' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Qualification',
        dataIndex: 'education_id',
        sorter: true,
      },
      {
        title: 'Institute',
        dataIndex: 'institute',
        sorter: true,
      },
      {
        title: 'Start Date',
        dataIndex: 'date_start',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
      {
        title: 'Completed On',
        dataIndex: 'date_end',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['education_id', {
        label: 'Qualification', type: 'select2', 'allow-null': false, 'remote-source': ['Education', 'id', 'name'],
      }],
      ['institute', { label: 'Institute', type: 'text', validation: '' }],
      ['date_start', { label: 'Start Date', type: 'date', validation: 'none' }],
      ['date_end', { label: 'Completed On', type: 'date', validation: 'none' }],
    ];
  }
}

/**
 * EmployeeCertificationAdapter
 */

class EmployeeCertificationAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'certification_id',
      'institute',
      'date_start',
      'date_end',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Certification' },
      { sTitle: 'Institute' },
      { sTitle: 'Granted On' },
      { sTitle: 'Valid Thru' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Certification',
        dataIndex: 'certification_id',
        sorter: true,
      },
      {
        title: 'Institute',
        dataIndex: 'institute',
        sorter: true,
      },
      {
        title: 'Granted On',
        dataIndex: 'date_start',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
      {
        title: 'Valid Thru',
        dataIndex: 'date_end',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['certification_id', {
        label: 'Certification', type: 'select2', 'allow-null': false, 'remote-source': ['Certification', 'id', 'name'],
      }],
      ['institute', { label: 'Institute', type: 'text', validation: '' }],
      ['date_start', { label: 'Granted On', type: 'date', validation: 'none' }],
      ['date_end', { label: 'Valid Thru', type: 'date', validation: 'none' }],
    ];
  }
}


/**
 * EmployeeLanguageAdapter
 */

class EmployeeLanguageAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'language_id',
      'reading',
      'speaking',
      'writing',
      'understanding',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Language' },
      { sTitle: 'Reading' },
      { sTitle: 'Speaking' },
      { sTitle: 'Writing' },
      { sTitle: 'Understanding' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Language',
        dataIndex: 'language_id',
        sorter: true,
      },
      {
        title: 'Reading',
        dataIndex: 'reading',
        sorter: true,
      },
      {
        title: 'Speaking',
        dataIndex: 'speaking',
        sorter: true,
      },
      {
        title: 'Writing',
        dataIndex: 'writing',
        sorter: true,
      },
      {
        title: 'Understanding',
        dataIndex: 'understanding',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    const compArray = [['Elementary Proficiency', 'Elementary Proficiency'],
      ['Limited Working Proficiency', 'Limited Working Proficiency'],
      ['Professional Working Proficiency', 'Professional Working Proficiency'],
      ['Full Professional Proficiency', 'Full Professional Proficiency'],
      ['Native or Bilingual Proficiency', 'Native or Bilingual Proficiency']];

    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['language_id', {
        label: 'Language', type: 'select2', 'allow-null': false, 'remote-source': ['Language', 'id', 'description'],
      }],
      ['reading', { label: 'Reading', type: 'select', source: compArray }],
      ['speaking', { label: 'Speaking', type: 'select', source: compArray }],
      ['writing', { label: 'Writing', type: 'select', source: compArray }],
      ['understanding', { label: 'Understanding', type: 'select', source: compArray }],
    ];
  }
}

module.exports = {
  EmployeeSkillAdapter,
  EmployeeEducationAdapter,
  EmployeeCertificationAdapter,
  EmployeeLanguageAdapter,
};
