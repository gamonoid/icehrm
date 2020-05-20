"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _de_DE = _interopRequireDefault(require("rc-pagination/lib/locale/de_DE"));

var _de_DE2 = _interopRequireDefault(require("../date-picker/locale/de_DE"));

var _de_DE3 = _interopRequireDefault(require("../time-picker/locale/de_DE"));

var _de_DE4 = _interopRequireDefault(require("../calendar/locale/de_DE"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var localeValues = {
  locale: 'de',
  Pagination: _de_DE["default"],
  DatePicker: _de_DE2["default"],
  TimePicker: _de_DE3["default"],
  Calendar: _de_DE4["default"],
  global: {
    placeholder: 'Bitte auswählen'
  },
  Table: {
    filterTitle: 'Filter-Menü',
    filterConfirm: 'OK',
    filterReset: 'Zurücksetzen',
    selectAll: 'Selektiere Alle',
    selectInvert: 'Selektion Invertieren',
    selectionAll: 'Wählen Sie alle Daten aus',
    sortTitle: 'Sortieren',
    expand: 'Zeile erweitern',
    collapse: 'Zeile reduzieren'
  },
  Modal: {
    okText: 'OK',
    cancelText: 'Abbrechen',
    justOkText: 'OK'
  },
  Popconfirm: {
    okText: 'OK',
    cancelText: 'Abbrechen'
  },
  Transfer: {
    searchPlaceholder: 'Suchen',
    itemUnit: 'Eintrag',
    itemsUnit: 'Einträge'
  },
  Upload: {
    uploading: 'Hochladen...',
    removeFile: 'Datei entfernen',
    uploadError: 'Fehler beim Hochladen',
    previewFile: 'Dateivorschau',
    downloadFile: 'Download-Datei'
  },
  Empty: {
    description: 'Keine Daten'
  },
  Text: {
    edit: 'Bearbeiten',
    copy: 'Kopieren',
    copied: 'Kopiert',
    expand: 'Erweitern'
  },
  PageHeader: {
    back: 'Zurück'
  }
};
var _default = localeValues;
exports["default"] = _default;