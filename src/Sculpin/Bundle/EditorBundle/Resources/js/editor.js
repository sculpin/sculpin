var SculpinEditor = {
    init: function () {
        this.renderBar();
    },

    renderBar: function () {
        console.log('Drawing Editor Bar');

        // Check if bar has already been drawn
        let menuBar = document.getElementById('SCULPIN_EDITOR_BAR');
        if (menuBar) {
            this.registerListeners();
            return;
        }

        // select body element for interface injection
        let body = document.getElementsByTagName('body')[0];

        // render menu bar with edit button
        body.innerHTML += '<div id="SCULPIN_EDITOR_BAR">' +
            '<div>' +
            '<a href="#edit" id="SCULPIN_EDIT_BUTTON">' +
            '<svg width="25px" height="25px" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">\n' +
            '<path fill-rule="evenodd" clip-rule="evenodd" d="M20.5337 3.3916C20.2236 3.08142 19.9559 2.81378 19.7193 2.60738C19.4702 2.39007 19.2019 2.1918 18.876 2.05679C18.1409 1.75231 17.3149 1.75231 16.5799 2.05679C16.2539 2.1918 15.9856 2.39007 15.7365 2.60738C15.4999 2.81378 15.2323 3.08141 14.9221 3.39159L8.93751 9.37615C8.52251 9.79078 8.20882 10.1042 7.97173 10.477C7.77111 10.7924 7.61569 11.1344 7.51002 11.4929C7.38514 11.9167 7.35534 12.3591 7.31592 12.9444L7.1842 14.8876C7.17485 15.0247 7.16396 15.1845 7.16666 15.3246C7.16974 15.4838 7.18962 15.7203 7.30999 15.9677C7.45687 16.2697 7.70083 16.5137 8.00282 16.6606C8.25029 16.7809 8.48679 16.8008 8.64598 16.8039C8.78602 16.8066 8.94585 16.7957 9.08298 16.7863L11.0261 16.6546C11.6114 16.6152 12.0539 16.5854 12.4776 16.4605C12.8362 16.3549 13.1782 16.1994 13.4936 15.9988C13.8664 15.7617 14.1798 15.448 14.5944 15.033L20.579 9.04845C20.8891 8.73829 21.1568 8.47067 21.3632 8.23405C21.5805 7.98491 21.7788 7.71662 21.9138 7.39069C22.2182 6.65561 22.2182 5.82968 21.9138 5.09459C21.7788 4.76867 21.5805 4.50038 21.3632 4.25124C21.1568 4.01464 20.8892 3.74704 20.579 3.43691L20.5337 3.3916ZM18.1106 3.90455C18.1522 3.92179 18.2324 3.96437 18.4046 4.11458C18.5836 4.27072 18.803 4.48928 19.1421 4.82843C19.4813 5.16758 19.6998 5.3869 19.856 5.56591C20.0062 5.73813 20.0488 5.81835 20.066 5.85996C20.1675 6.10499 20.1675 6.3803 20.066 6.62533C20.0488 6.66694 20.0062 6.74716 19.856 6.91938C19.7482 7.04288 19.6108 7.18558 19.4245 7.37359L16.597 4.54602C16.785 4.35976 16.9277 4.22231 17.0512 4.11458C17.2234 3.96437 17.3036 3.92179 17.3452 3.90455C17.5903 3.80306 17.8656 3.80306 18.1106 3.90455ZM15.1823 5.9598L18.0107 8.78823L13.2465 13.5525C12.7366 14.0624 12.5842 14.207 12.4202 14.3112C12.2625 14.4116 12.0915 14.4893 11.9122 14.5421C11.7258 14.597 11.5167 14.6168 10.7973 14.6655L9.19649 14.7741L9.30502 13.1732C9.3538 12.4538 9.37351 12.2447 9.42845 12.0583C9.48128 11.879 9.55899 11.708 9.6593 11.5503C9.76359 11.3863 9.90816 11.234 10.418 10.7241L15.1823 5.9598Z" fill="currentColor"/>\n' +
            '<path d="M11.0055 2C9.61949 1.99999 8.51721 1.99999 7.62839 2.0738C6.71811 2.14939 5.94253 2.30755 5.23415 2.67552C4.1383 3.24478 3.24477 4.1383 2.67552 5.23416C2.30755 5.94253 2.14939 6.71811 2.0738 7.6284C1.99999 8.51721 1.99999 9.61949 2 11.0055V12.9945C1.99999 14.3805 1.99999 15.4828 2.0738 16.3716C2.14939 17.2819 2.30755 18.0575 2.67552 18.7659C3.24477 19.8617 4.1383 20.7552 5.23415 21.3245C5.94253 21.6925 6.71811 21.8506 7.62839 21.9262C8.5172 22 9.61946 22 11.0054 22H13.0438C14.4068 22 15.4909 22 16.3654 21.9286C17.261 21.8554 18.0247 21.7023 18.7239 21.346C19.8529 20.7708 20.7708 19.8529 21.346 18.7239C21.7023 18.0247 21.8554 17.261 21.9286 16.3654C22 15.4909 22 14.4069 22 13.0439V13C22 12.4477 21.5523 12 21 12C20.4477 12 20 12.4477 20 13C20 14.4166 19.9992 15.419 19.9352 16.2026C19.8721 16.9745 19.7527 17.4457 19.564 17.816C19.1805 18.5686 18.5686 19.1805 17.816 19.564C17.4457 19.7527 16.9745 19.8721 16.2026 19.9352C15.419 19.9992 14.4166 20 13 20H11.05C9.60949 20 8.59025 19.9992 7.79391 19.9331C7.00955 19.8679 6.53142 19.7446 6.1561 19.5497C5.42553 19.1702 4.82985 18.5745 4.45035 17.8439C4.25538 17.4686 4.13208 16.9905 4.06694 16.2061C4.0008 15.4097 4 14.3905 4 12.95V11.05C4 9.60949 4.0008 8.59026 4.06694 7.79392C4.13208 7.00955 4.25538 6.53142 4.45035 6.15611C4.82985 5.42553 5.42553 4.82985 6.1561 4.45035C6.53142 4.25539 7.00955 4.13208 7.79391 4.06694C8.59025 4.00081 9.60949 4 11.05 4H12C12.5523 4 13 3.55229 13 3C13 2.44772 12.5523 2 12 2L11.0055 2Z" fill="currentColor"/>\n' +
            '</svg>' +
            'Edit This Page</a>' +
            '<a href="https://sculpin.io/documentation/sources">' +
            '<svg width="25px" height="25px" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">\n' +
            '<path d="M13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17Z" fill="currentColor"/>\n' +
            '<path d="M10.25 9.625C10.25 8.77087 10.9892 8 12 8C13.0108 8 13.75 8.77087 13.75 9.625C13.75 10.2116 13.4112 10.7484 12.8646 11.038C12.6027 11.1768 12.2205 11.3827 11.8927 11.7044C11.3217 12.2646 11 13.0309 11 13.8308V14C11 14.5523 11.4477 15 12 15C12.5523 15 13 14.5523 13 14V13.8308C13 13.5679 13.1057 13.3161 13.2933 13.132C13.3915 13.0357 13.5355 12.9459 13.801 12.8053C14.9448 12.1992 15.75 11.0147 15.75 9.625C15.75 7.57964 14.0267 6 12 6C9.97328 6 8.25 7.57964 8.25 9.625C8.25 10.1773 8.69772 10.625 9.25 10.625C9.80228 10.625 10.25 10.1773 10.25 9.625Z" fill="currentColor"/>\n' +
            '<path fill-rule="evenodd" clip-rule="evenodd" d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4Z" fill="currentColor"/>\n' +
            '</svg>' +
            'Documentation</a>' +
            '</div>' +
            '<div>' +
            '<span><strong>Current Disk Path:</strong> '+SCULPIN_EDITOR_METADATA.diskPath+'</span>' +
            '</div>' +
            '<div class="gap"></div>' +
            '<div class="right padr"><strong>Sculpin</strong> <em>In-Browser Editor</em></div> ' +
            '</div>';

        this.registerListeners();
    },

    renderEditor: function () {
        console.log('Drawing Editor Panel');

        // Check if editor has already been drawn
        let editor = document.getElementById('SCULPIN_EDIT_PANEL');
        if (editor) {
            this.registerEditorListeners();
            return;
        }

        // load editor box with content
        let body = document.getElementsByTagName('body')[0];
        body.innerHTML += '<div id="SCULPIN_EDIT_PANEL">' +
            '<div class="top-controls">' +
            '<div><h3>Editing:</h3>' +
            '<form><select id="SCULPIN_FILE_SELECTOR" name="file-selector">' +
            Object.entries(SCULPIN_EDITOR_METADATA.sourceMap).map(
                (item, i) => '<option value="' + item[0] + '"' + (item[0] === SCULPIN_EDITOR_METADATA.diskPath ? ' SELECTED' : '') + '>' + item[0] + '</option>'
            ).join('') +
            '</select></form>' +
            '</div>' +
            '<div class="gap"></div>' +
            '<div class="right"><strong>Sculpin</strong> <em>In-Browser Editor</em></div> ' +
            '</div>' +

            '<textarea id="SCULPIN_EDIT_TEXTAREA">' + SCULPIN_EDITOR_METADATA.content + '</textarea>' +

            '<div class="controls">' +
            '<button id="SCULPIN_CANCEL_CHANGES">Cancel</button> ' +
            '<button id="SCULPIN_SAVE_CHANGES">Save Changes</button>' +
            '</div></div>';

        this.registerEditorListeners();
    },

    registerListeners: function () {
        console.log('Registering Edit Bar Listeners');
        let editButton = document.getElementById("SCULPIN_EDIT_BUTTON");

        editButton && editButton.addEventListener('click', function () {
            console.log('Clicked Edit');
            SculpinEditor.renderEditor();

            var editor = document.getElementById('SCULPIN_EDIT_PANEL');
            var menuBar = document.getElementById('SCULPIN_EDITOR_BAR');

            if (editor) {
                console.log('Hiding Editor Bar');
                menuBar.style.visibility = 'collapse';
                editor.style.visibility = 'visible';
            }
        });
    },

    registerEditorListeners: function () {
        console.log('Registering Listeners');
        let saveButton = document.getElementById("SCULPIN_SAVE_CHANGES");
        let cancelButton = document.getElementById("SCULPIN_CANCEL_CHANGES");
        let fileSelectorForm = document.getElementById('SCULPIN_FILE_SELECTOR');

        saveButton && saveButton.addEventListener('click', function () {
            console.log('Clicked Save');
            SculpinEditor.saveChanges();
        });

        fileSelectorForm && fileSelectorForm.addEventListener('change', function (e) {
            console.log('Clicked to Change File');
            SculpinEditor.switchFile(e);
        });

        // @todo Cancel button re-registration is not working
        cancelButton && cancelButton.addEventListener('click', function () {
            console.log('Clicked Cancel');

            // @todo check if the content has changed and ask the user to confirm if they want to discard their changes
            document.getElementById('SCULPIN_EDITOR_BAR').style.visibility = 'visible';
            document.getElementById('SCULPIN_EDIT_PANEL').style.visibility = 'collapse';

            SculpinEditor.renderBar();
        });
    },

    saveChanges: function () {
        console.log('saving ...', SCULPIN_EDITOR_METADATA.diskPath, SCULPIN_EDITOR_METADATA.url);
        var content = document.getElementById('SCULPIN_EDIT_TEXTAREA').value;

        if (content === SCULPIN_EDITOR_METADATA.content) {
            // nothing has changed
            // pretend like something changed
            document.location.reload();
            return;
        }

        // PUT content to the appropriate spot
        // this logic is temporary. Would be nice to use local storage to make sure that nothing gets lost if
        // user navs away.
        fetch('/_SCULPIN_/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                'diskPath': SCULPIN_EDITOR_METADATA.diskPath,
                'url': SCULPIN_EDITOR_METADATA.url,
                'path': window.location.pathname,
                'content': content,
                'contentHash': SCULPIN_EDITOR_METADATA.contentHash
            })
        }).then(response => {
            if (response.ok) {
                SculpinEditor.watchForChanges(SCULPIN_EDITOR_METADATA.url, SCULPIN_EDITOR_METADATA.contentHashGenerated);

                return;
            }

            throw Error(response.statusText);
        }).catch(err => {
            console.log('Update failed: ' + err.message);

            // @todo come up with a nicer failure-handler than this ...
            document.location.reload();
        });
    },

    // Watches the hash every 500ms for 10 attempts, then gives up and reloads
    watchForChanges: function (url, oldHash) {
        let hashwatcherId;
        let hashwatcherCounter = 0;

        hashwatcherId = setInterval(() => {
            if (url.length === 0 || oldHash === undefined) {
                // The edited content does not correspond to a specific URL
                // Wait a few seconds and then trigger a regular reload
                clearInterval(hashwatcherId);
                setTimeout(() => document.location.reload(), 3000);
                return;
            }

            // @todo maybe update the document.location if the `url` is not blank & doesn't
            //       match (or isn't contained in) document.location
            if (hashwatcherCounter++ > 10) {
                console.log('Giving up on checking the hash; reloading current location');
                clearInterval(hashwatcherId);
                document.location.reload();
            }

            fetch('/_SCULPIN_/hash?url=' + url + '&oldHash=' + oldHash, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    // fetch that body
                    return response.json();
                }
            }).then(data => {
                console.log('Old Hash: ' + oldHash + ' New Hash: ' + data.hash + ' For URL: ' + url, data);

                // check that hash has changed from oldHash
                // if so, reload the current page
                if (data.hash !== oldHash) {
                    document.location.reload();
                }
            })
        }, 500);
    },

    switchFile: (e) => {
        // Ensure that the selected value exists in the SourceMap (source/* files, unprocessed) or PathMap (processed files mapped to the SourceMap)
        let newFilePath = e.currentTarget.value;
        let newFileSource = SCULPIN_EDITOR_METADATA.sourceMap[newFilePath];

        if (newFileSource === undefined) {
            console.log('Encountered an error: source map not found for ' + newFilePath + ', CANNOT SWITCH FILE');
        }

        // Check that it is OK to rewrite the Editor (i.e., the content matches the current content hash)
        let content = document.getElementById('SCULPIN_EDIT_TEXTAREA').value;
        if (content !== SCULPIN_EDITOR_METADATA.content) {
            console.log('Encountered an error: CONTENT MISMATCH, EDITOR IS DIRTY, CANNOT SWITCH FILE');
            return;
        }

        // Re-fetch Metadata Var for the new selection
        fetch('/_SCULPIN_/metadata?source=' + newFilePath)
            .then(response => {
                if (!response.ok) {
                    throw Error(response.statusText);
                }

                return response.json();
            })
            .then(data => {
                SCULPIN_EDITOR_METADATA = data;

                // Write new content to Editor Textarea
                SculpinEditor.refreshEditor();
            })
            .catch(err => console.log('Exception while updating metadata', err));
    },
    refreshEditor: () => {
        console.log('Refreshing the Editor Contents & Metadata');
        let editBox = document.getElementById('SCULPIN_EDIT_TEXTAREA');
        let editFilename = document.querySelectorAll('#SCULPIN_EDIT_PANEL > h3 > small')[0];

        editBox.value = SCULPIN_EDITOR_METADATA.content;
        editFilename.innerHTML = SCULPIN_EDITOR_METADATA.diskPath;
    }
};

// "you might not need jquery": https://youmightnotneedjquery.com/#ready
function ready(fn)
{
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

ready(() => SculpinEditor.init());
