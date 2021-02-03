
export type Photo = {
    id: number,
    title: string,
    hash: string,
    width: number,
    height: number,
    aspect: number,
    size: number,
    tiny: string,
}

export type Album = {
    title: string,
    description: string,
    photos: Photo[]
}