import ReactModalAdapterBase from './ReactModalAdapterBase';

class ReactLegacyModalAdapterBase extends ReactModalAdapterBase {
  // Due to having same method in ReactModalAdapterBase
  get(callBackData) {
    const that = this;

    if (this.getRemoteTable()) {
      this.createTableServer(this.getTableName());
      $(`#${this.getTableName()}Form`).hide();
      $(`#${this.getTableName()}`).show();
      return;
    }

    let sourceMappingJson = JSON.stringify(this.getSourceMapping());

    let filterJson = '';
    if (this.getFilter() !== null) {
      filterJson = JSON.stringify(this.getFilter());
    }

    let orderBy = '';
    if (this.getOrderBy() !== null) {
      orderBy = this.getOrderBy();
    }

    sourceMappingJson = this.fixJSON(sourceMappingJson);
    filterJson = this.fixJSON(filterJson);

    that.showLoader();
    $.post(this.moduleRelativeURL, {
      t: this.table, a: 'get', sm: sourceMappingJson, ft: filterJson, ob: orderBy,
    }, (data) => {
      if (data.status === 'SUCCESS') {
        that.getSuccessCallBack(callBackData, data.object);
      } else {
        that.getFailCallBack(callBackData, data.object);
      }
    }, 'json')
      .fail((e) => {
        if (e.status === 403) {
          that.showMessage('Access Forbidden', e.responseJSON.message);
        }
      })
      .always(() => { that.hideLoader(); });

    that.initFieldMasterData();

    this.trackEvent('get', this.tab, this.table);
    // var url = this.getDataUrl();
    // console.log(url);
  }

  showFilters(object) {
    let formHtml = this.templates.filterTemplate;
    let html = '';
    const fields = this.getFilters();

    for (let i = 0; i < fields.length; i++) {
      const metaField = this.getMetaFieldForRendering(fields[i][0]);
      if (metaField === '' || metaField === undefined) {
        html += this.renderFormField(fields[i]);
      } else {
        const metaVal = object[metaField];
        if (metaVal !== '' && metaVal != null && metaVal !== undefined && metaVal.trim() !== '') {
          html += this.renderFormField(JSON.parse(metaVal));
        } else {
          html += this.renderFormField(fields[i]);
        }
      }
    }
    formHtml = formHtml.replace(/_id_/g, `${this.getTableName()}_filter`);
    formHtml = formHtml.replace(/_fields_/g, html);

    const randomFormId = this.generateRandom(14);
    const $tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
    $tempDomObj.attr('id', randomFormId);

    $tempDomObj.html(formHtml);


    $tempDomObj.find('.datefield').datepicker({ viewMode: 2 });
    $tempDomObj.find('.timefield').datetimepicker({
      language: 'en',
      pickDate: false,
    });
    $tempDomObj.find('.datetimefield').datetimepicker({
      language: 'en',
    });

    $tempDomObj.find('.colorpick').colorpicker();
    tinymce.init({
      selector: `#${$tempDomObj.attr('id')} .tinymce`,
      height: '400',
    });

    $tempDomObj.find('.simplemde').each(function () {
      const simplemde = new SimpleMDE({ element: $(this)[0] });
      $(this).data('simplemde', simplemde);
      // simplemde.value($(this).val());
    });

    // $tempDomObj.find('.select2Field').select2();
    $tempDomObj.find('.select2Field').each(function () {
      $(this).select2().select2('val', $(this).find('option:eq(0)').val());
    });

    $tempDomObj.find('.select2Multi').each(function () {
      $(this).select2().on('change', function (e) {
        const parentRow = $(this).parents('.row');
        const height = parentRow.find('.select2-choices').height();
        parentRow.height(parseInt(height, 10));
      });
    });

    /*
         $tempDomObj.find('.signatureField').each(function() {
         $(this).data('signaturePad',new SignaturePad($(this)));
         });
         */

    // var tHtml = $tempDomObj.wrap('<div>').parent().html();
    this.showDomElement('Edit', $tempDomObj, null, null, true);
    $('.filterBtn').off();
    $('.filterBtn').on('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      try {
        modJs.filterQuery();
      } catch (err) {
        // Do Nothing
      }
      return false;
    });

    if (this.filter !== undefined && this.filter != null && this.filter !== '') {
      this.fillForm(this.filter, `#${this.getTableName()}_filter`, this.getFilters());
    }
  }

  resetFilters() {
    this.filter = this.origFilter;
    this.filtersAlreadySet = false;
    $(`#${this.getTableName()}_resetFilters`).hide();
    this.currentFilterString = '';
    this.get([]);
  }
}

export default ReactLegacyModalAdapterBase;
