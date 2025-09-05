<!DOCTYPE html>
<html>
<head>
    <title>Data Import & Export</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        h2 {
            margin-top: 30px;
        }
        form {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ddd;
            width: 400px;
            background: #f9f9f9;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        select, input, button {
            margin-top: 5px;
            padding: 6px;
            width: 100%;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Data Import & Export System</h1>

    <!-- Import Form -->
    <h2>Import Data</h2>
    <form action="{{ url('/data/import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="model">Select Model</label>
        <select name="model" id="model" required>
            <option value="User">User</option>
            <!-- Add more models if needed -->
        </select>

        <label for="file">Choose File (CSV/JSON)</label>
        <input type="file" name="file" id="file" required>

        <button type="submit">Import</button>
    </form>

    <!-- Export Form -->
    <h2>Export Data</h2>
    <form action="{{ url('/data/export') }}" method="POST">
        @csrf
        <label for="model">Select Model</label>
        <select name="model" id="model" required>
            <option value="User">User</option>
        </select>

        <label for="format">Select Format</label>
        <select name="format" id="format" required>
            <option value="csv">CSV</option>
            <option value="json">JSON</option>
            <option value="xml">XML</option>
        </select>

        <label for="filename">File Name</label>
        <input type="text" name="filename" id="filename" placeholder="users_export" required>

        <button type="submit">Export</button>
    </form>
</body>
</html>
