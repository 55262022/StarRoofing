export function calculateArea(length: number, width: number): number {
    return length * width;
}

export function calculateVolume(length: number, width: number, height: number): number {
    return length * width * height;
}

export function estimateMaterials(length: number, width: number, height: number): { area: number; volume: number; materials: { [key: string]: number } } {
    const area = calculateArea(length, width);
    const volume = calculateVolume(length, width, height);
    
    // Example estimation logic for materials
    const materials = {
        shingles: area * 0.1, // Example: 0.1 bundles of shingles per square meter
        insulation: volume * 0.05 // Example: 0.05 cubic meters of insulation per cubic meter
    };

    return { area, volume, materials };
}