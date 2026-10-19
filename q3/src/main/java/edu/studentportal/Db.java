package edu.studentportal;

import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

/** Central JDBC configuration. Environment variables take precedence over db.properties. */
public final class Db {
    private static final Properties PROPS = new Properties();

    static {
        try (InputStream in = Db.class.getClassLoader().getResourceAsStream("db.properties")) {
            if (in != null) PROPS.load(in);
            Class.forName("com.mysql.cj.jdbc.Driver");
        } catch (IOException | ClassNotFoundException ex) {
            throw new ExceptionInInitializerError(ex);
        }
    }

    private Db() {}

    private static String setting(String env, String key) {
        String value = System.getenv(env);
        return value != null ? value : PROPS.getProperty(key, "");
    }

    public static Connection getConnection() throws SQLException {
        String url = setting("STUDENT_DB_URL", "db.url");
        String user = setting("STUDENT_DB_USER", "db.user");
        String password = setting("STUDENT_DB_PASSWORD", "db.password");
        return DriverManager.getConnection(url, user, password);
    }

    public static String uploadDirectory() {
        return setting("STUDENT_UPLOAD_DIR", "upload.dir");
    }
}
