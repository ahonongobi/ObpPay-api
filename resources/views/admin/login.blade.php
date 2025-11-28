<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <form method="POST" action="{{ route('admin.login.submit') }}" 
          class="bg-white p-8 rounded shadow w-96">

        @csrf

        <h1 class="text-xl font-bold mb-6">Admin Login</h1>

        <label class="block mb-2">Phone</label>
        <input type="text" name="phone" class="w-full p-2 border rounded mb-4">

        <label class="block mb-2">Password</label>
        <input type="password" name="password" class="w-full p-2 border rounded mb-4">

        @error('phone')
            <div class="text-red-600 mb-2">{{ $message }}</div>
        @enderror

        <button class="bg-blue-600 text-white w-full py-2 rounded">
            Login
        </button>

    </form>

</body>
</html>
