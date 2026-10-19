package edu.studentportal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;

/** Demo login to demonstrate HttpSession protection. Change credentials before real use. */
@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.getRequestDispatcher("/WEB-INF/jsp/login.jsp").forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws IOException, ServletException {
        String username = request.getParameter("username");
        String password = request.getParameter("password");
        // Assignment demo only. Production systems should use hashed credentials in a database.
        if ("admin".equals(username) && "ChangeMe123!".equals(password)) {
            HttpSession session = request.getSession(true);
            session.setAttribute("loggedInUser", username);
            session.setMaxInactiveInterval(20 * 60);
            response.sendRedirect(request.getContextPath() + "/students");
        } else {
            request.setAttribute("error", "Invalid username or password.");
            request.getRequestDispatcher("/WEB-INF/jsp/login.jsp").forward(request, response);
        }
    }
}
