<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.12.0/dist/axios.min.js"></script>
</head>
<body>

<div class="container">
    <h1>Student dashboard</h1>
    <p>Place here your front end app for <strong>student</strong>: <strong>/resources/views/student/dashboard.blade.php</strong>
        <br>Js is here: <strong>/public/js/</strong></p>

    <div id="app">
        <p>@{{ message }}</p>
        <button v-on:click="onNotification">On Notification</button>
        <button v-on:click="offNotification">Off Notification</button>

        <p>@{{ chat }}</p>
        <button v-on:click="addMessage">Add test message</button>
    </div>

    <hr>
    <p>So far the is no groups checked. If you login you can access everything.</p>
    <p>
        <a href="/admin/advisers">Admin Panel</a><br>
        <a href="/adviser">Adviser Panel (login as adviser/director)</a><br>
        <a href="/student">Student Panel (login as student)</a><br>
        <a href="/logout">Logout</a><br>
    </p>
</div>
<script>
    var app5 = new Vue({
        el: '#app',
        data: {
            message: '',
            chat: '',
        },
        methods: {
            onNotification: function () {
                axios.post('/student/notification',{
                    notification: 1
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            },

            offNotification: function () {
                axios.post('/student/notification',{
                    notification: 0
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            },

            addMessage: function () {
                axios.post('/student/messages',{
                    message: 'Lorem ipsum dollar emet.'
                }).then(response => this.chat = response.data)
                    .catch(error => console.error(error));
            },
        }
    })
</script>
</body>
</html>