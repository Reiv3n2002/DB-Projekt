module.exports = {
  php:
    process.platform === "win32"
      ? "C:\\xampp\\php\\php.exe" // Windows Pfad
      : "/usr/bin/php", // Linux/MacOS Pfad
};
