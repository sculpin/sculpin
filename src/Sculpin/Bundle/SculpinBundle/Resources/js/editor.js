var SculpinEditor = {
    init: function () {
        this.renderBar();
    },

    renderBar: function () {
        // select body element
        var body = document.getElementsByTagName('body')[0];

        // render bottom bar with edit button
        body.innerHTML += '<div style="background-color: steelblue; padding: 20px; margin: 0; position: absolute; bottom: 0; left: 0; right: 0;" id="SCULPIN_BOTTOM_BAR">' +
            '<div style="float: left; color: white; font-family: sans-serif;font-size: 1.1em;"><strong><em style="text-transform: uppercase; padding-right: 20px;">Sculpin</em></strong>' +
            '<a href="https://sculpin.io/documentation/sources" style="color: white;">Documentation</a></div>' +
            '<div style="float: right;">' +
            '<span style="color: white; font-family: sans-serif; padding-right: 20px;"><strong>Current Disk Path:</strong> /'+SCULPIN_EDITOR_METADATA.diskPath+'</span>' +
            '<BUTTON id="SCULPIN_EDIT_BUTTON" style="color: #ffffff; background-color: #9f1770; border: 0; padding: 10px; font-weight: bolder;">Edit This Page</BUTTON></div>' +
            '</div>';
        // width: 100%; background-color: steelblue; padding-top: 20px; padding-bottom: 20px; position: fixed; bottom: 0px; left: 0px; padding-left: 0px; margin: 0px !important; display: none;
        this.registerListeners();
    },

    renderEditor: function () {
        // replace bar with editor
        document.getElementById('SCULPIN_BOTTOM_BAR').style.display = 'none';

        // load editor box with content
        var body = document.getElementsByTagName('body')[0];
        body.innerHTML += '<div id="SCULPIN_EDIT_PANEL" style="background-color: steelblue; padding: 12px; position: fixed; bottom: 0; height: 50%; left: 0; right: 0;">' +
            '<h3 style="color: white; font-family: sans-serif;">Editing <small>/'+SCULPIN_EDITOR_METADATA.diskPath+'</small></h3>' +
            '<textarea style="width: 100%; font-size: 1.02em; font-family: \'Roboto Mono\', monospace; padding-bottom: 12px; height: 70%;" id="SCULPIN_EDIT_TEXTAREA">' + SCULPIN_EDITOR_METADATA.content + '</textarea>' +
            '<div style="margin-top: 8px;"><button id="SCULPIN_SAVE_CHANGES" style="color: #ffffff; background-color: #9f1770; border: 0; padding: 10px; margin-right: 12px;font-weight: bolder;">Save Changes</button>' +
            '<button id="SCULPIN_CANCEL_CHANGES" style="color: #ffffff; background-color: #9f480c; border: 0; padding: 10px; margin-top: 10px;font-weight: bolder;">Cancel</button></div>' +
            '</div>';

        this.registerListeners();
    },

    registerListeners: function () {
        let editButton = document.getElementById("SCULPIN_EDIT_BUTTON");
        let saveButton = document.getElementById("SCULPIN_SAVE_CHANGES");
        let cancelButton = document.getElementById("SCULPIN_CANCEL_CHANGES");

        editButton && editButton.addEventListener('click', function () {
            var editor = document.getElementById('SCULPIN_EDIT_PANEL');
            var menuBar = document.getElementById('SCULPIN_BOTTOM_BAR');

            if (editor && editor.length > 0) {
                editor.style.display = 'block';
                menuBar.style.display = 'none';

                SculpinEditor.registerListeners();

                return;
            }

            SculpinEditor.renderEditor();
        });

        // create save button listener

        saveButton && saveButton.addEventListener('click', function () {
            SculpinEditor.saveChanges();
        });

        cancelButton && cancelButton.addEventListener('click', function () {
            // @todo check if the content has changed and ask the user to confirm if they want to discard their changes
            document.getElementById('SCULPIN_BOTTOM_BAR').style.display = 'block';
            document.getElementById('SCULPIN_EDIT_PANEL').style.display = 'none';

            SculpinEditor.registerListeners();
        });
    },

    saveChanges: function () {
        var content = document.getElementById('SCULPIN_EDIT_TEXTAREA').value;

        // PUT content to the appropriate spot
        // this logic is temporary. Would be nice to use local storage to make sure that nothing gets lost if
        // user navs away. Also, XHR synchronous usage is deprecated, would be nice to either redo this as a
        // form submit or await the result of the async version.
        var xmlHttp = new XMLHttpRequest();

        xmlHttp.open('PUT', window.location.href, false);
        xmlHttp.setRequestHeader('Content-Type', 'application/json');
        xmlHttp.send(JSON.stringify({'diskPath': SCULPIN_EDITOR_METADATA.diskPath,'content': content}));

        document.location.reload();
    }
};

SculpinEditor.init();