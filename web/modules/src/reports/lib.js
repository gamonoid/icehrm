/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import {
  ReportAdapter,
} from '../../../admin/src/reports/lib';


/**
 * UserReportAdapter
 */

class UserReportAdapter extends ReportAdapter {
  renderForm(object) {
    const that = this;
    this.processFormFieldsWithObject(object);
    if (this.remoteFieldsExists) {
      const cb = function () {
        that.renderFormNew(object);
      };
      this.initFieldMasterData(cb);
    } else {
      this.initFieldMasterData();
      that.renderFormNew(object);
    }

    this.currentReport = object;
  }
}

module.exports = { UserReportAdapter };
