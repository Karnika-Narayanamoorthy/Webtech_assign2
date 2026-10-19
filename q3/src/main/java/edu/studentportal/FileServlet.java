package edu.studentportal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.*;
import java.nio.file.*;
import java.sql.*;

/** Streams a database-linked file only after AuthFilter confirms the user's session. */
@WebServlet("/file")
public class FileServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        String rawId = request.getParameter("id");
        final int id;
        try { id = Integer.parseInt(rawId); }
        catch (Exception ex) { response.sendError(400, "Invalid file ID."); return; }

        String storedName;
        String originalName;
        String mime;
        try (Connection con = Db.getConnection();
             PreparedStatement ps = con.prepareStatement(
                 "SELECT stored_name, original_name, mime_type FROM student_files WHERE id = ?")) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                if (!rs.next()) { response.sendError(404, "File not found."); return; }
                storedName = rs.getString("stored_name");
                originalName = rs.getString("original_name");
                mime = rs.getString("mime_type");
            }
        } catch (SQLException ex) {
            getServletContext().log("Unable to retrieve file metadata", ex);
            response.sendError(500, "Could not retrieve file metadata.");
            return;
        }

        // Use only the stored basename; never accept a filesystem path from the request.
        if (storedName == null || !storedName.matches("[a-f0-9]{32}\\.(jpg|jpeg|png|pdf)")) {
            response.sendError(404, "Invalid stored filename."); return;
        }
        Path base = Paths.get(Db.uploadDirectory()).toAbsolutePath().normalize();
        Path path = base.resolve(storedName).normalize();
        if (!path.startsWith(base) || !Files.isRegularFile(path)) {
            response.sendError(404, "Stored file not found."); return;
        }

        response.setHeader("X-Content-Type-Options", "nosniff");
        response.setContentType(mime == null ? "application/octet-stream" : mime);
        boolean image = mime != null && mime.startsWith("image/");
        String safeName = originalName == null ? "download" : originalName.replaceAll("[\\r\\n\"\\\\]", "_");
        response.setHeader("Content-Disposition", (image ? "inline" : "attachment") + "; filename=\"" + safeName + "\"");
        response.setContentLengthLong(Files.size(path));
        try (InputStream in = Files.newInputStream(path); OutputStream out = response.getOutputStream()) {
            in.transferTo(out);
        }
    }
}
