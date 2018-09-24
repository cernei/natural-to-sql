<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Natural Language Query Tool</title>

    <script>
        var base = '/natural-to-sql'; // leave empty for root

        var _table_ = document.createElement('table'),
            _tr_ = document.createElement('tr'),
            _th_ = document.createElement('th'),
            _td_ = document.createElement('td');

        _table_.className = 'table';

        // Builds the HTML Table out of myList json data from Ivy restful service.
        function buildHtmlTable(arr) {
            var table = _table_.cloneNode(false),
                columns = addAllColumnHeaders(arr, table);
            for (var i = 0, maxi = arr.length; i < maxi; ++i) {
                var tr = _tr_.cloneNode(false);
                for (var j = 0, maxj = columns.length; j < maxj; ++j) {
                    var td = _td_.cloneNode(false);
                    cellValue = arr[i][columns[j]];
                    td.appendChild(document.createTextNode(arr[i][columns[j]] || ''));
                    tr.appendChild(td);
                }
                table.appendChild(tr);
            }
            return table;
        }

        // Adds a header row to the table and returns the set of columns.
        // Need to do union of keys from all records as some records may not contain
        // all records
        function addAllColumnHeaders(arr, table) {
            var columnSet = [],
                tr = _tr_.cloneNode(false);
            for (var i = 0, l = arr.length; i < l; i++) {
                for (var key in arr[i]) {
                    if (arr[i].hasOwnProperty(key) && columnSet.indexOf(key) === -1) {
                        columnSet.push(key);
                        var th = _th_.cloneNode(false);
                        th.appendChild(document.createTextNode(key));
                        tr.appendChild(th);
                    }
                }
            }
            table.appendChild(tr);
            return columnSet;
        }

        var input;
        var cursor = 0;
        var queryHistory = [];
        var output;

        function send() {
            input = document.querySelector('#query');
            output = document.querySelector('#result');
            var queryLog = document.querySelector('#queryLog');
            var currentQuery = document.querySelector('#currentQuery');

            while (output.firstChild) {
                output.removeChild(output.firstChild);
            }

            var query = input.value;
            queryHistory.push(query);
            input.value = '';
            cursor = 0;

            fetch(base + '/ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}, // this line is important, if this content-type is not set it wont work
                body: 'q=' + query
            }).then(function (response) {

                response.json().then(function (json) {
                    var toAppend;
                    if (json.status == 'error') {
                        toAppend = document.createElement('h3');
                        toAppend.innerHTML = json.message;
                    } else {
                        if (json.type == 'value') {
                            toAppend = document.createElement('h1');
                            toAppend.innerHTML = json.result;
                        } else if (json.type == 'list') {
                            toAppend = buildHtmlTable(json.result);
                        } else {
                            toAppend = buildHtmlTable(json.result);
                        }
                    }


                    output.appendChild(toAppend);

                    var text = document.createElement('div');
                    text.innerHTML = query;

                    queryLog.appendChild(text);

                    currentQuery.innerHTML = query;
                })
            });
            return false;
        }

        document.onkeydown = function (e) {

            input = document.querySelector('#query');

            e = e || window.event;

            switch (e.which || e.keyCode) {
                case 38 :
                    e.preventDefault();
                    if (cursor < queryHistory.length) {
                        cursor += 1;
                        input.value = queryHistory[queryHistory.length - cursor];
                    }
                    break;
                case 40 :
                    e.preventDefault();
                    if (cursor > 0) {
                        cursor -= 1;
                        if (cursor == 0) {
                            input.value = '';
                        } else {
                            input.value = queryHistory[queryHistory.length - cursor];
                        }
                    }
                    break;
            }
        }


    </script>
</head>
<body>
<div class="container">

    <div class="row">
        <div class="col-12">
            <form action="" onSubmit="return send()">
                <input type="text" id="query" autocomplete="off" class="form-control mt-5 mb-3"
                       placeholder="Type query here">
            </form>
            <div class="py-2">Query: <strong id="currentQuery"></strong></div>
        </div>
    </div>
    <div class="row">
        <div class="col-8">
            <div id="result" class="table-responsive"></div>
        </div>
        <div class="col-4">
            <div id="queryLog" style="height: 300px; overflow-y: scroll;"></div>
        </div>
    </div>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
</body>
</html>