import React, { useEffect, useRef, useState } from 'react';

import EditorJS from '@editorjs/editorjs';
import Embed from '@editorjs/embed';
import Table from '@editorjs/table';
import Paragraph from '@editorjs/paragraph';
import List from '@editorjs/list';
// import Warning from '@editorjs/warning';
import Code from '@editorjs/code';
// import LinkTool from '@editorjs/link';
import Raw from '@editorjs/raw';
import Header from '@editorjs/header';
import Quote from '@editorjs/quote';
import Marker from '@editorjs/marker';
import CheckList from '@editorjs/checklist';
// import Delimiter from '@editorjs/delimiter';
import InlineCode from '@editorjs/inline-code';

export const EDITOR_JS_TOOLS = {
  embed: Embed,
  table: {
    class: Table,
    inlineToolbar: true,
  },
  paragraph: {
    class: Paragraph,
    inlineToolbar: true,
  },
  list: {
    class: List,
    inlineToolbar: true,
  },
  // warning: {
  //   class: Warning,
  //   inlineToolbar: true
  // },
  code: {
    class: Code,
    inlineToolbar: true,
  },
  // linkTool: {
  //   class: LinkTool,
  //   inlineToolbar: true
  // },
  raw: {
    class: Raw,
    inlineToolbar: true,
  },
  header: {
    class: Header,
    inlineToolbar: true,
  },
  quote: {
    class: Quote,
    inlineToolbar: true,
  },
  marker: {
    class: Marker,
    inlineToolbar: true,
  },
  checklist: {
    class: CheckList,
    inlineToolbar: true,
  },
  // delimiter: {
  //   class: Delimiter,
  //   inlineToolbar: true,
  // },
  inlineCode: {
    class: InlineCode,
    inlineToolbar: true,
  },
};

const parseValue = (value) => {
  let checkValue = {
    // time: (new Date()).getTime(),
    version: '2.19.3',
    blocks: [
      {
        type: 'paragraph',
        data: {
          text: value,
        },
      },
    ],
  };

  try {
    if (value && typeof value === 'object' && value.time) {
      checkValue = value;
    } else if (value && typeof value === 'string') {
      const jsonValue = JSON.parse(value);
      if (jsonValue.blocks) {
        checkValue = jsonValue;

        if (checkValue.blocks.length === 0) {
          checkValue.blocks = [
            {
              type: 'paragraph',
              data: {
                text: '',
              },
            },
          ];
        }
      }
    }
  } catch (e) {
  }

  return checkValue;
};

function IceEditor(props) {
  const { value, onChange, readOnly = false } = props;
  const [keepValue, setKeepValue] = useState(value || '');
  const [editor, setEditor] = useState(null);
  const [editorLoading, setEditorLoading] = useState(true);
  const editorBlock = useRef(null);

  useEffect(() => {
    const editorJs = new EditorJS({
      holder: editorBlock.current,
      tools: EDITOR_JS_TOOLS,
      onChange: (value) => value.saver.save().then((value) => {
        onChange(JSON.stringify(value));
      }),
      onReady: () => {
        setEditor(editorJs);
        setEditorLoading(false);
      },
      data: {},
      readOnly,
    });

    return () => {
      if (editor && typeof editor.destroy === 'function') {
        editor.destroy();
      }
    };
  }, []);

  useEffect(() => {
    if (value && !keepValue) {
      setKeepValue(value);
    }
  }, [value]);

  useEffect(() => {
    if (!editorLoading && keepValue) {
      editor.render(parseValue(keepValue));
    }
  }, [editorLoading, keepValue]);

  return <div ref={editorBlock} />;
}

export default IceEditor;
