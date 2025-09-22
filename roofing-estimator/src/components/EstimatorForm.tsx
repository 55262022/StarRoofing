import React, { useState } from 'react';

const EstimatorForm: React.FC = () => {
    const [length, setLength] = useState<number | ''>('');
    const [width, setWidth] = useState<number | ''>('');
    const [height, setHeight] = useState<number | ''>('');
    const [error, setError] = useState<string>('');
    
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (validateInputs()) {
            // Trigger estimation calculations
            // Call the estimation function here
        }
    };

    const validateInputs = () => {
        if (length <= 0 || width <= 0 || height <= 0) {
            setError('All dimensions must be positive numbers.');
            return false;
        }
        setError('');
        return true;
    };

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <label htmlFor="length">Length (ft):</label>
                <input
                    type="number"
                    id="length"
                    value={length}
                    onChange={(e) => setLength(Number(e.target.value))}
                    required
                />
            </div>
            <div>
                <label htmlFor="width">Width (ft):</label>
                <input
                    type="number"
                    id="width"
                    value={width}
                    onChange={(e) => setWidth(Number(e.target.value))}
                    required
                />
            </div>
            <div>
                <label htmlFor="height">Height (ft):</label>
                <input
                    type="number"
                    id="height"
                    value={height}
                    onChange={(e) => setHeight(Number(e.target.value))}
                    required
                />
            </div>
            {error && <p style={{ color: 'red' }}>{error}</p>}
            <button type="submit">Estimate Materials</button>
        </form>
    );
};

export default EstimatorForm;