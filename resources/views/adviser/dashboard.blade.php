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

        <p>@{{ chat }}</p>
        <button v-on:click="addMessage">Add test message</button>

        <p>@{{ note }}</p>
        <button v-on:click="addPublicNote">Add Public Note</button>
        <button v-on:click="rmPublicNote">Rm Public Note</button>
        <button v-on:click="addPrivateNote">Add Private Note</button>
        <button v-on:click="rmPrivateNote">Rm Private Note</button>

        <p>@{{ period }}</p>
        <button v-on:click="addPeriod">Add Period</button>
        <button v-on:click="rmPeriod">Rm Period</button>
        <button v-on:click="notifyPeriod">Notify Period</button><br>
        <button v-on:click="addTimeslot">Add Timeslot</button>
        <button v-on:click="updateTimeslot">Update Timeslot</button>
        <button v-on:click="rmTimeslot">Rm Timeslot</button><br>
        <button v-on:click="cancelReservation">Cancel Reservation</button>
        <button v-on:click="attendReservation">Attend Reservation</button>
        <button v-on:click="missReservation">Miss Reservation</button>

        <p>@{{ settings }}</p>
        <button v-on:click="setSettings">Update Settings</button>
    </div>

    <hr>
    <p>So far the is no groups checked. If you login you can access everything.</p>
    <p>
        <a href="/api/admin/advisers">Admin Panel</a><br>
        <a href="/api/adviser">Adviser Panel</a><br>
        <a href="/api/student">Student Panel</a><br>
        <a href="/api/logout">Logout</a><br>
    </p>
</div>
<script>
    var app5 = new Vue({
        el: '#app',
        data: {
            message: '',
            chat: '',
            note: '',
            period: '',
            settings: '',
        },
        methods: {
            assignStudents: function () {

                const adviserId = 702;
                const studentIds = [963, 964, 965, 960];

                axios.post('/api/director/assign',{
                    adviserId: adviserId,
                    studentIds: studentIds,
                }).then(response => this.message = response.data)
                  .catch(error => console.error(error));
            },

            dismissStudents: function () {

                const studentIds = [963, 964, 965, 960];

                axios.post('/api/director/dismiss',{
                    studentIds: studentIds
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            },

            addMessage: function () {
                axios.post('/api/adviser/messages',{
                    studentId: 609,
                    message: 'Lorem ipsum dollar emet 222.'
                }).then(response => this.chat = response.data)
                    .catch(error => console.error(error));
            },

            addPublicNote: function () {
                axios.post('/api/adviser/notes/public',{
                    studentId: 609,
                    note: 'Public Note TESTTT'
                }).then(response => this.note = response.data)
                    .catch(error => console.error(error));
            },

            addPrivateNote: function () {
                axios.post('/api/adviser/notes/private',{
                    studentId: 609,
                    note: 'Private Note TESTTT'
                }).then(response => this.note = response.data)
                    .catch(error => console.error(error));
            },

            rmPublicNote: function () {
                axios.post('/api/adviser/notes/public/remove',{
                    student_id: 609,
                    note_id: 10
                }).then(response => this.note = response.data)
                    .catch(error => console.error(error));
            },

            rmPrivateNote: function () {
                axios.post('/api/adviser/notes/private/remove',{
                    student_id: 609,
                    note_id: 10
                }).then(response => this.note = response.data)
                    .catch(error => console.error(error));
            },

            addPeriod: function () {
                axios.post('/api/director/periods',{
                    startDate: '2019-04-10',
                    endDate: '2019-04-31'
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            notifyPeriod: function () {
                axios.post('/api/director/period/notify').then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            setSettings: function () {
                axios.post('/api/adviser/settings',{
                    phone: '0999111887',
                    office: 'VMS9997',
                    is_notification: 0,
                    interval: 40
                }).then(response => this.settings = response.data)
                    .catch(error => console.error(error));
            },

            rmPeriod: function () {
                axios.post('/api/director/period/remove',{
                    id: 8,
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            addTimeslot: function () {
                axios.post('/api/adviser/timeslots/2019-10-15',{
                    time: '12:00',
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            updateTimeslot: function () {
                axios.post('/api/adviser/timeslots/2019-05-02/update',{
                    timeslots: ['13:00','13:30','14:00','14:30'],
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            rmTimeslot: function () {
                axios.post('/api/adviser/timeslot/remove',{
                    timeslot_id: 28,
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            cancelReservation: function () {
                axios.post('/api/adviser/reservation/cancel',{
                    reservation_id: 22,
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            attendReservation: function () {
                axios.post('/api/adviser/reservation/attend',{
                    reservation_id: 22,
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },

            missReservation: function () {
                axios.post('/api/adviser/reservation/miss',{
                    reservation_id: 9,
                }).then(response => this.period = response.data)
                    .catch(error => console.error(error));
            },
        }
    })
</script>
</body>
</html>