/*
Nice in theory but this won't render my inputs. I'm sure this works somehow
but with my current knowledge I don't use components

Vue.component('chat', {
    props: ['status'],
    template: `
        <div id="chatwindow">
            <ul>
                <li v-for="message in status">
                    <strong>{{ message.username }}:</strong> {{ message.posted }}<br>
                    {{ message.message }}
                </li>
            </ul>
        </div>
    `
})
*/

var baseUrl = 'http://chat.local';


var app = new Vue({
    el: '#chat',
    /*
    component: [
        'chat'
    ],
    */
    data: {
        ajaxUrl: baseUrl + '/chat.php',
        chatActive: true,
        chatMessages: [],
        dateOptions: {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: false,
            //timeZone: 'UTC'
            timeZone: 'Europe/Berlin',
        },
        errors: [],
        message: '',
        userlistActive: false,
        username: '',
        users: []
    },
    created: function() {
        this.loadInitialMessages();
        this.getUsers();
    },
    mounted: function() {
        setInterval(function() {
            this.getUsers();
            this.loadMessages();
        }.bind(this), 3000);
    },
    filters: {
        decode: function(value) {
            if (value) {
                return he.decode(value);
            }
            return null;
        }
    },
    methods: {
        getUsers: function() {
            fetch(this.ajaxUrl, {
                method: 'POST',
                mode: "same-origin",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'getUsers',
                })
            }).then(response => response.json())
            .then(json => {
                this.users = json.data
            })
            .catch(error => console.error(error));
        },
        loadInitialMessages: function() {
            this.chatMessages = 'Loading ...';

            let date = new Date();
            let dateString = new Intl.DateTimeFormat('de-DE', this.dateOptions).format(date);

            fetch(this.ajaxUrl)
                .then(response => response.json())
                .then(json => {
                    this.chatMessages = json.data.messages;
                    this.username = json.data.username;
                })
                .catch(error => console.error(error));
        },
        loadMessages: function() {
            /* Redirect to login page if session is expired */
            if (this.username == '') {
                window.location.replace(baseUrl);
            }
            let date = new Date();
            let dateString = new Intl.DateTimeFormat('de-DE', this.dateOptions).format(date);

            let lastId = 0;
            for (let i = this.chatMessages.length - 1; i > 0; i--) {
                if (typeof this.chatMessages[i].id !== 'undefined' && this.chatMessages[i].id > 0) {
                    lastId = this.chatMessages[i].id;
                    break;
                }
            }

            fetch(this.ajaxUrl, {
                method: 'POST',
                mode: "same-origin",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'loadMessages',
                    lastId: lastId,
                })
            })
            .then(response => response.json())
            .then(json => {
                if (json.errors) {
                    this.errors = json.errors;
                }

                if (json.data) {
                    for (let i = 0; i < json.data.length; i++) {
                        if (json.data[i].username != this.username) {
                            this.chatMessages.push(json.data[i]);
                        }
                    }
                }
                let chatwindow = this.$el.querySelector("#chatwindow");
                chatwindow.scrollTop = chatwindow.scrollHeight;
            })
            .catch(error => console.error(error));
        },
        postMessage: function() {
            let date = new Date();
            options = {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit',
                hour12: false,
                timeZone: 'Europe/Berlin'
            };
            let dateStringDE = new Intl.DateTimeFormat('de-DE', options).format(date);
            let dateStringUTC = new Intl.DateTimeFormat('de-DE', this.dateOptions).format(date);

            if (!this.chatMessages) {
                this.chatMessages = [{
                    username: this.username,
                    posted: dateStringDE,
                    message: this.message
                }];
            } else {
                this.chatMessages.push({
                    username: this.username,
                    posted: dateStringDE,
                    message: this.message
                });
            }
            fetch(this.ajaxUrl, {
                method: 'POST',
                mode: "same-origin",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'postMessage',
                    data: {
                        username: this.username,
                        posted: dateStringUTC,
                        message: this.message
                    }
                })
            }).then(response => response.json())
            .then(json => {
                if (json.errors) {
                    this.errors = json.errors;
                }
            })
            .catch(error => console.error(error));
            this.message = "";
        },
        showChat: function() {
            this.chatActive = true;
            this.userlistActive = false;
        },
        showUsers: function() {
            this.userlistActive = true;
            this.chatActive = false;
        }
    }
});
