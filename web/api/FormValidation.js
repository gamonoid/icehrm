/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global tinyMCE */
const ValidationRules = {

  float(str) {
    const floatstr = /^[-+]?[0-9]+(\.[0-9]+)?$/;
    if (str != null && str.match(floatstr)) {
      return true;
    }
    return false;
  },

  number(str) {
    const numstr = /^[0-9]+$/;
    if (str != null && str.match(numstr)) {
      return true;
    }
    return false;
  },

  numberOrEmpty(str) {
    if (str === '') {
      return true;
    }
    const numstr = /^[0-9]+$/;
    if (str != null && str.match(numstr)) {
      return true;
    }
    return false;
  },

  email(str) {
    const emailPattern = /^\s*[\w\-+_]+(\.[\w\-+_]+)*@[\w\-+_]+\.[\w\-+_]+(\.[\w\-+_]+)*\s*$/;
    return str != null && emailPattern.test(str);
  },

  emailOrEmpty(str) {
    if (str === '') {
      return true;
    }
    const emailPattern = /^\s*[\w\-+_]+(\.[\w\-+_]+)*@[\w\-+_]+\.[\w\-+_]+(\.[\w\-+_]+)*\s*$/;
    return str != null && emailPattern.test(str);
  },

  username(str) {
    const username = /^[a-zA-Z0-9.-]+$/;
    return str != null && username.test(str);
  },

  input(str) {
    if (str != null && str.length > 0) {
      return true;
    }
    return false;
  },


};

class FormValidation {
  constructor(formId, validateAll, options) {
    this.tempOptions = {};
    this.formId = formId;
    this.formError = false;
    this.formObject = null;
    this.errorMessages = '';
    this.popupDialog = null;
    this.validateAll = validateAll;
    this.errorMap = [];

    this.settings = { thirdPartyPopup: null, LabelErrorClass: false, ShowPopup: true };

    this.settings = jQuery.extend(this.settings, options);

    this.inputTypes = ['text', 'radio', 'checkbox', 'file', 'password', 'select-one', 'select-multi', 'textarea', 'fileupload', 'signature'];

    this.validator = ValidationRules;
  }

  // eslint-disable-next-line no-unused-vars
  clearError(formInput, overrideMessage) {
    const id = formInput.attr('id');
    $(`#${this.formId} #field_${id}`).removeClass('error');
    $(`#${this.formId} #help_${id}`).html('');
  }

  // eslint-disable-next-line no-unused-vars
  addError(formInput, overrideMessage) {
    this.formError = true;
    if (formInput.attr('message') != null) {
      this.errorMessages += (`${formInput.attr('message')}\n`);
      this.errorMap[formInput.attr('name')] = formInput.attr('message');
    } else {
      this.errorMap[formInput.attr('name')] = '';
    }

    const id = formInput.attr('id');
    const validation = formInput.attr('validation');
    const message = formInput.attr('validation');
    $(`#${this.formId} #field_${id}`).addClass('error');
    if (message === undefined || message == null || message === '') {
      $(`#${this.formId} #help_err_${id}`).html(message);
    } else if (validation === undefined || validation == null || validation === '') {
      $(`#${this.formId} #help_err_${id}`).html('Required');
    } else if (validation === 'float' || validation === 'number') {
      $(`#${this.formId} #help_err_${id}`).html('Number required');
    } else if (validation === 'email') {
      $(`#${this.formId} #help_err_${id}`).html('Email required');
    } else {
      $(`#${this.formId} #help_err_${id}`).html('Required');
    }
  }


  showErrors() {
    if (this.formError) {
      if (this.settings.thirdPartyPopup !== undefined && this.settings.thirdPartyPopup != null) {
        this.settings.thirdPartyPopup.alert();
      } else if (this.settings.ShowPopup === true) {
        if (this.tempOptions.popupTop !== undefined && this.tempOptions.popupTop != null) {
          this.alert('Errors Found', this.errorMessages, this.tempOptions.popupTop);
        } else {
          this.alert('Errors Found', this.errorMessages, -1);
        }
      }
    }
  }


  checkValues(options) {
    this.tempOptions = options;
    const that = this;
    this.formError = false;
    this.errorMessages = '';
    this.formObject = {};
    // eslint-disable-next-line consistent-return
    const validate = function (inputObject) {
      let inputValue = null;
      const name = inputObject.attr('name');
      if (that.settings.LabelErrorClass !== false) {
        $(`label[for='${name}']`).removeClass(that.settings.LabelErrorClass);
      }
      const id = inputObject.attr('id');
      const type = inputObject.attr('type');

      if (inputObject.hasClass('select2-focusser') || inputObject.hasClass('select2-input')) {
        return true;
      }

      if (jQuery.inArray(type, that.inputTypes) >= 0) {
        if (inputObject.hasClass('uploadInput')) {
          inputValue = inputObject.attr('val');
        } else if (type === 'radio' || type === 'checkbox') {
          inputValue = $(`input[name='${name}']:checked`).val();
        } else if (inputObject.hasClass('select2Field')) {
          if ($(`#${that.formId} #${id}`).select2('data') != null && $(`#${that.formId} #${id}`).select2('data') !== undefined) {
            inputValue = $(`#${that.formId} #${id}`).select2('data').id;
          } else {
            inputValue = '';
          }
        } else if (inputObject.hasClass('select2Multi')) {
          if ($(`#${that.formId} #${id}`).select2('data') != null && $(`#${that.formId} #${id}`).select2('data') !== undefined) {
            const inputValueObjects = $(`#${that.formId} #${id}`).select2('data');
            inputValue = [];
            for (let i = 0; i < inputValueObjects.length; i++) {
              inputValue.push(inputValueObjects[i].id);
            }
            inputValue = JSON.stringify(inputValue);
          } else {
            inputValue = '';
          }
        } else if (inputObject.hasClass('signatureField')) {
          if ($(`#${that.formId} #${id}`).data('signaturePad').isEmpty()) {
            inputValue = '';
          } else {
            inputValue = $(`#${id}`).data('signaturePad').toDataURL();
          }
        } else if (inputObject.hasClass('simplemde')) {
          inputValue = $(`#${that.formId} #${id}`).data('simplemde').value();
        } else if (inputObject.hasClass('code')) {
          inputValue = $(`#${that.formId} #${id}`).data('codemirror').getValue();
        } else if (inputObject.hasClass('tinymce')) {
          inputValue = tinyMCE.get(id).getContent({ format: 'raw' });
        } else {
          inputValue = inputObject.val();
        }

        const validation = inputObject.attr('validation');
        let valid = false;

        if (validation !== undefined && validation != null && that.validator[validation] !== undefined && that.validator[validation] != null) {
          valid = that.validator[validation](inputValue);
        } else {
          if (that.validateAll) {
            if (validation !== undefined && validation != null && validation === 'none') {
              valid = true;
            } else {
              valid = that.validator.input(inputValue);
            }
          } else {
            valid = true;
          }
          that.formObject[id] = inputValue;
        }

        if (!valid) {
          that.addError(inputObject, null);
        } else {
          that.clearError(inputObject, null);
          that.formObject[id] = inputValue;
        }
      }
    };

    let inputs = $(`#${this.formId} :input`);
    inputs.each(function () {
      validate($(this));
    });

    inputs = $(`#${this.formId} .uploadInput`);
    inputs.each(function () {
      validate($(this));
    });

    this.showErrors();
    this.tempOptions = {};
    return !this.formError;
  }

  getFormParameters() {
    return this.formObject;
  }


  alert(title, text) {
    alert(text);
  }

  static getValidationRules() {
    return ValidationRules;
  }
}


export default FormValidation;
