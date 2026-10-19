package edu.studentportal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

@WebServlet("/students")
public class StudentServlet extends HttpServlet {
    public static class Student {
        public int id;
        public String name, rollNumber, email, course;
        public List<StudentFile> files = new ArrayList<>();
    }
    public static class StudentFile {
        public int id;
        public String originalName, mimeType, category;
        public long size;
        public Timestamp uploadedAt;
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        List<Student> students = new ArrayList<>();
        String sql = "SELECT id, name, roll_number, email, course FROM students ORDER BY name";
        try (Connection con = Db.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                Student s = new Student();
                s.id = rs.getInt("id");
                s.name = rs.getString("name");
                s.rollNumber = rs.getString("roll_number");
                s.email = rs.getString("email");
                s.course = rs.getString("course");
                students.add(s);
            }
            String fileSql = "SELECT id, student_id, original_name, mime_type, file_category, file_size, uploaded_at FROM student_files ORDER BY uploaded_at DESC";
            try (PreparedStatement fps = con.prepareStatement(fileSql);
                 ResultSet frs = fps.executeQuery()) {
                while (frs.next()) {
                    int studentId = frs.getInt("student_id");
                    for (Student s : students) {
                        if (s.id == studentId) {
                            StudentFile f = new StudentFile();
                            f.id = frs.getInt("id");
                            f.originalName = frs.getString("original_name");
                            f.mimeType = frs.getString("mime_type");
                            f.category = frs.getString("file_category");
                            f.size = frs.getLong("file_size");
                            f.uploadedAt = frs.getTimestamp("uploaded_at");
                            s.files.add(f);
                            break;
                        }
                    }
                }
            }
        } catch (SQLException ex) {
            getServletContext().log("Unable to load student records", ex);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Could not load students. Check database settings.");
            return;
        }
        request.setAttribute("students", students);
        request.getRequestDispatcher("/WEB-INF/jsp/students.jsp").forward(request, response);
    }
}
