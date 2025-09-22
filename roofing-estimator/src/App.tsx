import React, { useState } from 'react';
import EstimatorForm from './components/EstimatorForm';
import Roof3DViewer from './components/Roof3DViewer';

const App: React.FC = () => {
    const [dimensions, setDimensions] = useState<{ length: number; width: number; height: number } | null>(null);

    const handleEstimation = (length: number, width: number, height: number) => {
        setDimensions({ length, width, height });
    };

    return (
        <div className="app-container">
            <h1>Roofing Estimator Tool</h1>
            <EstimatorForm onEstimate={handleEstimation} />
            {dimensions && <Roof3DViewer dimensions={dimensions} />}
        </div>
    );
};

export default App;