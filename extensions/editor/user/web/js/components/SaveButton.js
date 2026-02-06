import React from 'react';
import {
  Button, message, Popconfirm,
} from 'antd';
import { SaveOutlined, DeleteOutlined } from '@ant-design/icons';

class SaveButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      saveLoading: false,
      deleteLoading: false,
      deleteConfirmOpen: false,
    };
  }

  showError() {
    message.error({
      content: 'Error Saving Data',
      key: 'editDocument',
      duration: 2,
      style: {
        marginTop: '7vh',
      },
    });
  }

  showDeleteConfirm() {
    this.setState({ deleteConfirmOpen: true });
  }

  handleDeleteCancel() {
    this.setState({ deleteConfirmOpen: false });
  }

  handleDeleteOk() {
    this.setState({ deleteLoading: true });

    setTimeout(() => {
      this.setState({ deleteConfirmOpen: false });
      this.setState({ deleteLoading: false });
    }, 2000);
  }

  saveContent() {
    this.setState({ saveLoading: true });
    if (window.editor_readonly) { editor.readOnly.toggle(false); }
    // this.sidebarReference.current.save();
    window.editor.save()
      .then((savedData) => {
        // cPreview.show(savedData, document.getElementById('output'));
        window.editorExtensionController.saveContent(window.hash, savedData)
          .then((response) => {
            this.setState({ saveLoading: false });
            if (response.status === 200) {
              message.success({
                content: 'Document saved',
                key: 'editDocument',
                duration: 2,
                style: {
                  marginTop: '7vh',
                },
              });
              if (window.editor_readonly) { editor.readOnly.toggle(true); }
            } else {
              if (window.editor_readonly) { editor.readOnly.toggle(true); }
              this.showError();
            }
          }).catch((error) => {
            if (window.editor_readonly) { editor.readOnly.toggle(true); }
            this.setState({ saveLoading: false });
            this.showError();
          });
      })
      .catch((error) => {
        if (window.editor_readonly) { editor.readOnly.toggle(true); }
        this.setState({ saveLoading: false });
        this.showError();
      });
  }

  render() {
    return (
      <Button
        type="primary"
        icon={<SaveOutlined />}
        loading={this.state.saveLoading}
        onClick={() => this.saveContent()}
      >
        {modJs.gt('Save')}
      </Button>
    );
  }
}

export default SaveButton;
