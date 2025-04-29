# POS React Native (Expo)

A React Native-based frontend application for the Point of Sale system, built with Expo.

## Prerequisites

Before you begin, ensure you have the following installed:
- [Node.js](https://nodejs.org/) (v14 or later)
- [npm](https://www.npmjs.com/) or [Yarn](https://yarnpkg.com/)
- [Expo CLI](https://docs.expo.dev/get-started/installation/)
- [Expo Go](https://expo.dev/client) (on your mobile device)

## Getting Started

1. **Install Expo CLI**
   ```bash
   npm install -g expo-cli
   ```

2. **Create New Project**
   ```bash
   npx create-expo-app pos-frontend
   cd pos-frontend
   ```

3. **Install Dependencies**
   ```bash
   npm install @reduxjs/toolkit react-redux axios @react-navigation/native @react-navigation/native-stack async-storage react-native-paper react-native-safe-area-context react-native-gesture-handler
   ```

4. **Start Development Server**
   ```bash
   npx expo start
   ```

5. **Run on Device**
   - Install Expo Go on your device
   - Scan QR code with camera (Android) or Expo Go app (iOS)
   - App will load on your device

## Project Structure

See [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) for detailed information about the project structure and architecture.

## Development Guidelines

1. **Code Style**
   - Follow React Native best practices
   - Use TypeScript for type safety
   - Follow ESLint and Prettier rules
   - Use functional components with hooks

2. **State Management**
   - Use Redux Toolkit for state management
   - Keep business logic in reducers
   - Use selectors for derived state

3. **Testing**
   - Use Expo Go for quick testing
   - Implement proper error boundaries
   - Use console logging for debugging

4. **API Integration**
   - Use Axios for HTTP requests
   - Implement proper error handling
   - Use interceptors for auth and logging

## Development Workflow

1. **Start Development Server**
   ```bash
   npx expo start
   ```

2. **Testing on Device**
   - Open Expo Go on your device
   - Scan the QR code
   - App will load with live reloading

3. **Debugging**
   - Use React Native Debugger
   - Console logging
   - Error boundaries

## Troubleshooting

Common issues and solutions:

1. **Expo Go Connection Issues**
   ```bash
   npx expo start --clear
   ```

2. **Dependency Issues**
   ```bash
   rm -rf node_modules
   npm install
   ```

3. **Cache Issues**
   ```bash
   npx expo start --clear
   ```

## Contributing

1. Create a feature branch
2. Implement your changes
3. Test on Expo Go
4. Create a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact [support-email] or create an issue in the repository. 