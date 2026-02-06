import { TYPES } from "./constants";

export function createButton() {
  const button = document.createElement("button");
  button.className = "qt-button";
  button.type = "button";
  return button;
}

export function createIconButton(config) {
  const { size = "small" } = config || {};
  const btn = document.createElement("button");
  btn.className = "icon-button";
  btn.classList.add(`icon-button__${size}`);
  btn.type = "button";
  return btn;
}

export function createVariant({ text, value, index }, name, config) {
  const {
    onInputChange,
    onTextChange,
    inputType,
    onDelete,
    checked,
    readOnly,
    isDeleteDisabled,
  } = config;

  // creating an item
  const item = document.createElement("div");
  item.className = "quiz-item";
  if (readOnly) item.classList.add("quiz-item__hoverable");
  item.tabIndex = "0";

  const label = document.createElement("label");
  label.classList.add("quiz-item__label");

  // creating a radio/checkbox
  const input = document.createElement("input");
  input.type = inputType;
  input.name = name;
  input.value = value;
  input.checked = checked;
  input.onclick = onInputChange;
  item.appendChild(input);

  // creating editable paragraph
  const paragraph = document.createElement("p");
  paragraph.innerHTML = text;
  paragraph.contentEditable = !readOnly;
  paragraph.className = "quiz-item__text";
  paragraph.setAttribute("data-index", index);
  paragraph.onblur = (event) => onTextChange(event, index);

  const checkmark = document.createElement("span");
  checkmark.classList.add(
    "quiz-item__checkmark",
    `quiz-item__checkmark--${inputType}`
  );

  label.appendChild(input);
  label.appendChild(checkmark);
  item.appendChild(label);
  item.appendChild(paragraph);

  // if read only mode select the item on click
  if (readOnly)
    item.onclick = (e) => {
      if (e.target.classList.contains("quiz-item__checkmark"))
        return e.stopPropagation();
      input.click();
    };

  if (!readOnly) {
    // creating a delete icon
    const deleteBtn = createIconButton();
    const deleteIcon = document.createElement("span");
    deleteIcon.innerHTML = "Delete";
    deleteBtn.appendChild(deleteIcon);
    deleteBtn.onclick = () => onDelete(index);
    deleteBtn.disabled = isDeleteDisabled;
    isDeleteDisabled && deleteBtn.classList.add("disabled");
    item.appendChild(deleteBtn);
  }

  return item;
}

export function renderSettings(settings, onClick, context) {
  const wrapper = document.createElement("div");

  settings.forEach((tune) => {
    const item = document.createElement("div");

    item.setAttribute("data-item-name", tune.name);
    item.classList.add("ce-popover-item", tune.className);
    item.onclick = () => {
      onClick(tune);
      item.classList.toggle(context.api.styles.settingsButtonActive);
    };

    // adding icon
    const icon = document.createElement("div");
    icon.classList.add("ce-popover-item__icon");
    icon.innerHTML = tune.icon;
    item.appendChild(icon);

    // adding title
    const title = document.createElement("div");
    title.classList.add("ce-popover-item__title");
    title.innerHTML = tune.title;
    item.appendChild(title);

    wrapper.appendChild(item);
  });

  return wrapper;
}

export function renderVariants(variants, type, context) {
  const variantsContainer = document.createElement("div");

  variants.forEach((variant, index) => {
    const item = createVariant({ ...variant, index }, context.block.id, {
      inputType: type === TYPES.multiSelect ? "checkbox" : "radio",
      checked: context.getAnswers().has(variant.value),
      readOnly: context.readOnly,
      onInputChange: context._variantInputChangeHandler,
      onTextChange: context._variantTextChangeHandler,
      onDelete: context._deleteVariant,
      isDeleteDisabled: variants.length <= context.validation.min,
    });
    variantsContainer.appendChild(item);
  });

  variantsContainer.onkeydown = (event) => {
    const { target, metaKey, ctrlKey } = event;
    const isItemTextElement = target.classList.contains("quiz-item__text");
    const index = event.target.dataset.index;

    // add a new variant if user hits the enter
    const isEnterClicked = event.key === "Enter" && event.code === "Enter";
    const isCommandAndEnter = (metaKey || ctrlKey) && isEnterClicked;
    if (isCommandAndEnter && isItemTextElement) {
      prevent(event);
      context._variantTextChangeHandler(event, index);
      return context._addVariant(Number(index) + 1);
    }

    // remove the item if value is empty when user click backspace
    const isBackspaceClicked =
      event.key === "Backspace" && event.code === "Backspace";
    const isEmpty = target.innerText === "";
    if (isBackspaceClicked && isItemTextElement && isEmpty) {
      prevent(event);
      context._deleteVariant(index);
    }
  };

  return variantsContainer;
}

export function prevent(event) {
  event.stopPropagation();
  event.preventDefault();
}

export function createLoader() {
  const loader = document.createElement("div");
  loader.className = "cdx-quiz-lds-ring";
  loader.innerHTML = "<div></div><div></div><div></div><div></div>";
  return loader;
}
