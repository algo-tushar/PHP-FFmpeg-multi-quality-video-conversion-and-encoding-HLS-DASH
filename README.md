# PHP FFmpeg Multi-Quality Video Conversion & Encoding for HLS/DASH

Welcome to our PHP FFmpeg project for seamless multi-quality video conversion and encoding, tailored specifically for generating HLS and DASH formats. 

## Overview

Our project aims to simplify the process of converting video files into multiple quality levels and encoding them into HLS (HTTP Live Streaming) and DASH (Dynamic Adaptive Streaming over HTTP) formats. With this tool, you can effortlessly prepare your videos for seamless streaming across various devices and network conditions.

## Features

- **User-Friendly Interface**: Enjoy a hassle-free experience with our intuitive interface designed for simplicity and efficiency.
- **Effortless Upload**: Easily upload your video files and initiate the conversion process with just a few clicks.
- **Automated Conversion Magic**: Our backend system handles the heavy lifting, automating the conversion process to save you time and effort.
- **Multi-Quality Encoding**: Generate video files in multiple quality levels to accommodate different devices and network speeds.
- **HLS and DASH Support**: Seamlessly encode your videos into HLS and DASH formats, ensuring compatibility with a wide range of platforms.
- **Convenient Packaging**: Download your converted videos conveniently packaged into a ZIP format for easy access and distribution.

## Getting Started

To get started with our project, follow these steps:

1. Clone the repository to your local machine.
2. Install the necessary dependencies as outlined in the `requirements.txt` file.
3. Configure the project settings by editing the `config.php` file as per your requirements.

### Configuring `config.php`

Before using the project, make sure to configure the `config.php` file. Here's what you need to do:

- Open the `config.php` file located in the project root directory.
- Set the appropriate values for `HOMEURL`, `ROOTPATH`, `UPLOADPATH`, and `LOGPATH` constants as per your server setup.
- Ensure that the upload and log directories are writable by the server.
- If necessary, update the paths to the FFMpeg and FFProbe binaries in the `$config` array.
- Save the changes to the `config.php` file.

## Contributions

We welcome contributions from the community to help improve and expand the capabilities of our project. Whether it's fixing bugs, adding new features, or enhancing documentation, your contributions are highly valued.

To contribute:
1. Fork the repository.
2. Make your changes.
3. Submit a pull request detailing your changes.

## Support

If you encounter any issues or have any questions, feel free to open an issue on the repository. We're here to help and provide support as needed.

## License

This project is licensed under the [MIT License](LICENSE), allowing for free and open use, modification, and distribution.

---

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)