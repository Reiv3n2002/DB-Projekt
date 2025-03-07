module.exports = {
  php:
    process.platform === "win32"
      ? "E:\\xampp\\php\\php.exe" // Windows Pfad
      : "/usr/bin/php", // Linux/MacOS Pfad
};
