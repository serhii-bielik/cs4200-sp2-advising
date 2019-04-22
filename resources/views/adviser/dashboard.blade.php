<!DOCTYPE html>
<html lang="en">
<head>
    <title>Adviser dashboard</title>
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
    <h1>Adviser dashboard</h1>

    <p>Place here your front end app for <strong>adviser/director</strong>: <strong>/resources/views/adviser/dashboard.blade.php</strong>
        <br>Js is here: <strong>/public/js/</strong></p>

    <div id="app">
        <p>@{{ message }}</p>
        <button v-on:click="assignStudents">Assign Students</button>
        <button v-on:click="dismissStudents">Dismiss Students</button>

        <p>@{{ notification }}</p>
        <button v-on:click="onNotification">On Notification</button>
        <button v-on:click="offNotification">Off Notification</button>

        <p>@{{ interval }}</p>
        <button v-on:click="interval20">20 Interval</button>
        <button v-on:click="interval30">30 Interval</button>

        <p>@{{ chat }}</p>
        <button v-on:click="addMessage">Add test message</button>

        <p>@{{ note }}</p>
        <button v-on:click="addPublicNote">Add Public Note</button>
        <button v-on:click="addPrivateNote">Add Private Note</button>

        <p>@{{ period }}</p>
        <button v-on:click="addPeriod">Add Period (Director API)</button>

        <p>@{{ settings }}</p>
        <button v-on:click="setSettings">Update Settings</button>
    </div>

    <hr>
    <p>So far the is no groups checked. If you login you can access everything.</p>
    <p>
        <a href="/admin/advisers">Admin Panel</a><br>
        <a href="/adviser">Adviser Panel</a><br>
        <a href="/student">Student Panel</a><br>
        <a href="/logout">Logout</a><br>
    </p>
</div>
<script>
    var app5 = new Vue({
        el: '#app',
        data: {
            message: '',
            chat: '',
            notification: '',
            interval: '',
            note: '',
            period: '',
            settings: '',
        },
        methods: {
            assignStudents: function () {

                const adviserId = 2;
                const studentIds = [334, 335, 336, 337, 338];

                axios.post('/director/assign',{
                    adviserId: adviserId,
                    studentIds: studentIds,
                }).then(response => this.message = response.data)
                  .catch(error => console.error(error));
            },

            dismissStudents: function () {

                const adviserId = 2;
                const studentIds = [334, 335, 336, 337, 338];

                axios.post('/director/dismiss',{
                    adviserId: adviserId,
                    studentIds: studentIds
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            },

            onNotification: function () {
                axios.post('/adviser/notification',{
                    notification: 1
                }).then(response => this.notification = response.data)
                    .catch(error => console.error(error));
            },

            offNotification: function () {
                axios.post('/adviser/notification',{
                    notification: 0
                }).then(response => this.notification = response.data)
                    .catch(error => console.error(error));
            },

            interval20: function () {
                axios.post('/adviser/interval',{
                    interval: 20
                }).then(response => this.interval = response.data)
                    .catch(error => console.error(error));
            },

            interval30: function () {
                axios.post('/adviser/interval',{
                    interval: 30
                }).then(response => this.interval = response.data)
                    .catch(error => console.error(error));
            },

            addMessage: function () {
                axios.post('/adviser/messages',{
                    studentId: 355,
                    message: 'Lorem ipsum dollar emet 222.'
                }).then(response => this.chat = response.data)
                    .catch(error => console.error(error));
            },

            addPublicNote: function () {
                axios.post('/adviser/notes/public',{
                    studentId: 355,
                    note: 'Public Note TESTTT'
                }).then(response => this.note = response.data)
                    .catch(error => console.error(error));
            },

            addPrivateNote: function () {
                axios.post('/adviser/notes/private',{
                    studentId: 355,
                    note: 'Private Note TESTTT'
                }).then(response => this.note = response.data)
                    .catch(error => console.error(error));
            },

            addPeriod: function () {
                axios.post('/director/periods',{
                    startDate: '2019-07-15',
                    endDate: '2019-07-25'
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            setSettings: function () {
                axios.post('/adviser/settings',{
                    phone: '0999111888',
                    office: 'VMS9999',
                    isNotification: 0,
                    interval: 30
                }).then(response => this.settings = response.data)
                    .catch(error => console.error(error));
            },
        }
    })
</script>
</body>
</html>