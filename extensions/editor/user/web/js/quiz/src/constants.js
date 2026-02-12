import { CheckboxIcon, QuizIcon, RadioInputIcon } from "./icons";

export const TYPES = {
  singleSelect: "singleSelect",
  multiSelect: "multiSelect",
};

export const settings = [
  {
    title: "Single Select",
    name: "single-select",
    type: TYPES.singleSelect,
    icon: RadioInputIcon,
    className: "qt-settings-icon__single",
  },
  {
    title: "Multi Select",
    name: "multi-select",
    type: TYPES.multiSelect,
    icon: CheckboxIcon,
    className: "qt-settings-icon__multi",
  },
];

export const Toolbox = {
  title: "Quiz",
  icon: QuizIcon,
};

export const LANGUAGES = ["uz", "en"];

export const TEXTS = {
  uz: {
    errors: {
      required: "Iltimos javobni tanlang",
    },
    footer: {
      submit: "Tekshirish",
    },
  },
  en: {
    errors: {
      required: "Please pick the answer",
    },
    footer: {
      submit: "Check Answer",
    },
  },
};
