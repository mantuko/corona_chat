<?php include_once 'config.php'; ?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <title>St. Franziskus Bochum - Chat zum Livestream</title>
        <link rel="apple-touch-icon" sizes="57x57" href="assets/grafix/favicon/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="assets/grafix/favicon/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="assets/grafix/favicon/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="assets/grafix/favicon/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="assets/grafix/favicon/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="assets/grafix/favicon/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="assets/grafix/favicon/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="assets/grafix/favicon/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="assets/grafix/favicon/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="assets/grafix/favicon/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/grafix/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="assets/grafix/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/grafix/favicon/favicon-16x16.png">
        <link rel="manifest" href="assets/grafix/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" href="assets/css/reset.css" type="text/css">
        <link rel="stylesheet" href="assets/css/style.css" type="text/css">
        <script src="node_modules/vue/dist/vue.min.js"></script>
        <script src="node_modules/he/he.js"></script>
    </head>
    <body>
        <div class="grid-container">
            <header>
                <h1><img src="assets/grafix/pentateuch.png" height="65px">&nbsp;St. Franziskus - Gottesdienst im Livestream</h1>
            </header>
            <main>
                <div class="video">
                    <div class="video_container">
                        <!-- Add a placeholder for the Twitch embed -->
                        <div id="twitch-embed"></div>
                    </div>
                </div>
                <div id="chat">
                    <div id="errors" v-show="errors.length > 0">
                        <ul>
                            <li v-for="error in errors">{{ error }}</li>
                        </ul>
                    </div>
                    <div class="headingWrapper" v-bind:class="{chatHeadingFix: chatActive}">
                        <h2 id="chatHeading" v-on:click="showChat" v-bind:class="{inactive: userlistActive}">Nachrichten</h2>
                        <h2 id="userlistHeading" v-on:click="showUsers" v-bind:class="{inactive: chatActive}">Teilnehmer*innen</h2>
                    </div>
                    <div id="chatwindow" v-show="chatActive">
                        <ul>
                            <li v-for="message in chatMessages">
                                <strong>{{ message.username }}:</strong> {{ message.posted }}
                                <p>{{ message.message | decode }}</p>
                            </li>
                        </ul>
                    </div>
                    <form v-show="chatActive">
                        <div class="innerFormWrapper">
                            <p id="chatnick" v-model="username" ><strong>{{ username }}:</strong></p>
                            <textarea id="chatmsg" v-model="message" v-on:keyup.enter="postMessage" rows="4" placeholder="Nachricht ..."></textarea>
                            <span>Absenden: Shift + Enter</span>
                            <input type="button" v-on:click="postMessage" value="absenden">
                            <!-- <input type="button" @:click="postMessage" @:keyup.enter="postMessage" :disabled="message.length < 3" value="+ add" onclick="submit_msg();"> -->

                            <!--  <chat :status="status" /> Chat as a vue component -->
                        </div>
                    </form>
                    <ul id="userlist" v-show="userlistActive">
                        <li v-for="user in users">{{ user[0] }}</li>
                    </ul>
                </div>
            </main>
            <footer>
                <p></p>
            </footer>
        </div>
        <!-- Load the Twitch embed script -->
        <script src="https://embed.twitch.tv/embed/v1.js"></script>

        <!-- Create a Twitch.Embed object that will render within the "twitch-embed" root element. -->
        <script type="text/javascript">
          new Twitch.Embed("twitch-embed", {
            width: '100%',
            height: '100%',
            channel: '<?php echo TWITCH_CHANNEL ?>',
            layout: 'video',
          });
        </script>
    </body>
</html>
<script src="assets/js/mychat.js"></script>
