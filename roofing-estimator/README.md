# Roofing Estimator Tool

## Overview
The Roofing Estimator Tool is a web application designed to help users estimate the materials needed for roofing projects based on user-provided dimensions (length, width, and height). The application features a 3D visualization of the roofing materials and integrates with an existing inventory system to manage product data.

## Features
- User input form for length, width, and height.
- Real-time validation of input dimensions.
- Calculation of required roofing materials based on input dimensions.
- 3D visualization of the roofing model using a rendering library.
- Integration with an existing inventory system to fetch and update product data.

## Project Structure
```
roofing-estimator
├── src
│   ├── components
│   │   ├── EstimatorForm.tsx
│   │   ├── Roof3DViewer.tsx
│   │   └── InventoryIntegration.ts
│   ├── utils
│   │   ├── calculations.ts
│   │   └── api.ts
│   ├── styles
│   │   └── main.css
│   ├── App.tsx
│   └── index.tsx
├── public
│   └── index.html
├── package.json
├── tsconfig.json
└── README.md
```

## Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   ```
2. Navigate to the project directory:
   ```
   cd roofing-estimator
   ```
3. Install the dependencies:
   ```
   npm install
   ```

## Usage
1. Start the development server:
   ```
   npm start
   ```
2. Open your browser and navigate to `http://localhost:3000` to access the application.

## Contributing
Contributions are welcome! Please open an issue or submit a pull request for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for details.