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

        <p>@{{ settings }}</p>
        <button v-on:click="setSettings">Settings</button>

        <p>@{{ timeslot }}</p>
        <button v-on:click="makeReservation">Reservation</button>
        <button v-on:click="makeReservationFlex">Reservation Flex</button>
        <button v-on:click="cancelReservation">Cancel Reservation</button><br>

        <button v-on:click="notificationRead">Read Notification</button>
        <button v-on:click="notificationReadAll">Read All Notifications</button><br>

    </div>

    <hr>
    <p>So far the is no groups checked. If you login you can access everything.</p>
    <p>
        <a href="/api/admin/advisers">Admin Panel</a><br>
        <a href="/api/adviser">Adviser Panel (login as adviser/director)</a><br>
        <a href="/api/student">Student Panel (login as student)</a><br>
        <a href="/api/logout">Logout</a><br>
    </p>
</div>
<script>
    var app5 = new Vue({
        el: '#app',
        data: {
            message: '',
            chat: '',
            settings: '',
            timeslot: '',
        },
        methods: {
            onNotification: function () {
                axios.post('/api/student/notification',{
                    notification: 1
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            },

            offNotification: function () {
                axios.post('/api/student/notification',{
                    notification: 0
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            },

            addMessage: function () {
                axios.post('/api/student/messages',{
                    message: 'Lorem ipsum dollar emet.'
                }).then(response => this.chat = response.data)
                    .catch(error => console.error(error));
            },

            setSettings: function () {
                axios.post('/api/student/settings',{
                    phone: '0111111111',
                    is_notification: 0,
                }).then(response => this.settings = response.data)
                    .catch(error => console.error(error));
            },

            makeReservation: function () {
                axios.post('/api/student/reservation/make',{
                    timeslot_id: 27,
                }).then(response => this.timeslot = response.data)
                    .catch(error => console.error(error));
            },

            makeReservationFlex: function () {
                axios.post('/api/student/reservation/make',{
                    date: '2019-05-29',
                    time: '9:00'
                }).then(response => this.timeslot = response.data)
                    .catch(error => console.error(error));
            },

            cancelReservation: function () {
                axios.post('/api/student/reservation/cancel',{
                    reservation_id: 22,
                }).then(response => this.timeslot = response.data)
                    .catch(error => console.error(error));
            },

            notificationRead: function () {
                axios.post('/api/notifications/read',{
                    notification_id: '70c08e6c-4689-404a-b8d8-a02be8a52fe7',
                }).then(response => this.timeslot = response.data)
                    .catch(error => console.error(error));
            },

            notificationReadAll: function () {
                axios.post('/api/notifications/readAll',{
                }).then(response => this.timeslot = response.data)
                    .catch(error => console.error(error));
            },
        }
    })
</script>
</body>
</html>