# POS Frontend

A Flutter-based frontend application for the Point of Sale system.

## Prerequisites

Before you begin, ensure you have the following installed:
- [Flutter SDK](https://docs.flutter.dev/get-started/install)
- [Android Studio](https://developer.android.com/studio) (for Android development)
- [VS Code](https://code.visualstudio.com/) (recommended IDE)
- [Git](https://git-scm.com/downloads)

## Getting Started

1. **Install Flutter**
   ```bash
   # Download Flutter SDK from https://docs.flutter.dev/get-started/install
   # Extract to a location like C:\src\flutter
   # Add Flutter to your PATH
   ```

2. **Install Android Studio**
   - Download and install Android Studio
   - Install Android SDK, SDK Command-line Tools, and Android Emulator
   - Set up an Android Virtual Device (AVD)

3. **Install VS Code Extensions**
   - Flutter
   - Dart
   - Flutter Widget Snippets
   - Bloc

4. **Clone the Repository**
   ```bash
   git clone [repository-url]
   cd pos/frontend
   ```

5. **Install Dependencies**
   ```bash
   flutter pub get
   ```

6. **Run the App**
   ```bash
   flutter run
   ```

## Project Structure

See [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) for detailed information about the project structure and architecture.

## Development Guidelines

1. **Code Style**
   - Follow Flutter's official style guide
   - Use meaningful variable and function names
   - Add documentation for public APIs

2. **State Management**
   - Use BLoC pattern for state management
   - Keep business logic in use cases
   - Separate UI from business logic

3. **Testing**
   - Write unit tests for business logic
   - Write widget tests for UI components
   - Write integration tests for complete features

4. **API Integration**
   - Use repository pattern
   - Implement proper error handling
   - Cache responses when appropriate

## Contributing

1. Create a feature branch
2. Implement your changes
3. Write tests
4. Create a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact [support-email] or create an issue in the repository. 