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
            message: ''
        },
        methods: {
            assignStudents: function () {

                const adviser = 2;
                const students = [334, 335, 336, 337, 338];

                axios.post('/director/assign',{
                    data: { adviser: adviser, students: students }
                }).then(response => this.message = response.data)
                  .catch(error => console.error(error));
            },

            dismissStudents: function () {

                const adviser = 2;
                const students = [334, 335, 336, 337, 338];

                axios.post('/director/dismiss',{
                    data: { adviser: adviser, students: students }
                }).then(response => this.message = response.data)
                    .catch(error => console.error(error));
            }
        }
    })
</script>
</body>
</html>