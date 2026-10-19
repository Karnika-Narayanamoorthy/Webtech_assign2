<%@ page contentType="text/html;charset=UTF-8" %>
<%@ page import="java.util.List,edu.studentportal.StudentServlet.Student,edu.studentportal.StudentServlet.StudentFile" %>
<%!
  // Escape user-controlled values before inserting them into HTML.
  private String esc(String value) {
    if (value == null) return "";
    return value.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
                .replace("\"", "&quot;").replace("'", "&#x27;");
  }
%>
<%
List<Student> students = (List<Student>) request.getAttribute("students");
String ctx = request.getContextPath();
%>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Profiles</title><link rel="stylesheet" href="<%= ctx %>/assets/style.css"></head>
<body><main class="shell"><header class="top"><div><p class="eyebrow">JAVA SERVLET + JDBC + JSP</p><h1>Student profiles</h1><p class="muted">Signed in as <%= esc((String) session.getAttribute("loggedInUser")) %></p></div>
<form method="post" action="<%= ctx %>/logout"><button class="secondary" type="submit">Log out</button></form></header>
<div class="grid">
<% if (students != null && !students.isEmpty()) { for (Student s : students) { %>
<article class="card student-card"><div class="avatar"><%= esc(s.name.isEmpty() ? "S" : s.name.substring(0,1).toUpperCase()) %></div>
<h2><%= esc(s.name) %></h2><p class="muted"><%= esc(s.rollNumber) %></p>
<dl><dt>Email</dt><dd><%= esc(s.email) %></dd><dt>Course</dt><dd><%= esc(s.course) %></dd></dl>
<h3>Files</h3>
<% if (s.files != null && !s.files.isEmpty()) { for (StudentFile f : s.files) { %>
<div class="file-row">
<% if (f.mimeType != null && f.mimeType.startsWith("image/")) { %>
<a href="<%= ctx %>/file?id=<%= f.id %>"><img class="thumb" src="<%= ctx %>/file?id=<%= f.id %>" alt="Uploaded image"></a>
<% } else { %><span class="file-badge">DOC</span><% } %>
<div><a href="<%= ctx %>/file?id=<%= f.id %>"><%= esc(f.originalName) %></a><p class="muted"><%= esc(f.category) %> · <%= f.size / 1024 %> KB</p></div>
</div>
<% } } else { %><p class="muted">No files uploaded.</p><% } %>
</article>
<% } } else { %><section class="card"><h2>No student records</h2><p>Add a student using the PHP admin panel, then refresh this page.</p></section><% } %>
</div><p class="hint">Student data is read from the shared MySQL database. Files are streamed through an authenticated Servlet.</p></main></body></html>
