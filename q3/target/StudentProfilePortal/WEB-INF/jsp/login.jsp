<%@ page contentType="text/html;charset=UTF-8" %>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Portal Login</title><link rel="stylesheet" href="${pageContext.request.contextPath}/assets/style.css"></head>
<body><main class="shell login-shell"><section class="card"><p class="eyebrow">STUDENT SERVICES</p><h1>Portal login</h1><p class="muted">Sign in to view student profiles and files.</p>
<% if (request.getAttribute("error") != null) { %><p class="error"><%= request.getAttribute("error") %></p><% } %>
<form method="post" action="${pageContext.request.contextPath}/login">
<label>Username<input name="username" autocomplete="username" required></label>
<label>Password<input type="password" name="password" autocomplete="current-password" required></label>
<button type="submit">Sign in</button></form>
<p class="hint">Demo credentials: admin / ChangeMe123!</p></section></main></body></html>
