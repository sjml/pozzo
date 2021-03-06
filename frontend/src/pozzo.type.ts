
export type SiteConfig = {
    apiUri: string,
    siteTitle: string|boolean,
    formats: string[],
    sizes: any[],
    promo: boolean,
    dynamicPublic: boolean,
    contentLicense: string,
    maxUploadBytes: number,
    simultaneousUploads: number,
}

export type ApiResult = {
    success: boolean,
    code: number,
    data: any,
}

export type ApiOptions = {
    authorize?: boolean,
    params?: object,
    method?: string,
}

export type FileUploadStatus = {
    file: File,
    status: number,
    index: number,
    targetAlbum?: number,
    startUploadCallback?: Function,
}

export type Photo = {
    // only things required to render
    id: number,
    hash: string,
    uniq: string,
    blurHash: string,
    aspect: number
    isVideo: boolean,

    // extended import data
    title: string,
    uploadTimeStamp: number,
    uploadedBy: number,
    originalFilename: string,
    size: number,
    width: number,
    height: number,
    tags: string[],

    // initially pulled from exif, so if the uploaded photo
    //   didn't have it, it ain't here
    make: string|null,
    model: string|null,
    lens: string|null,
    mime: string|null,
    creationDate: number|null,
    subjectArea: string|null,
    aperture: string|null,
    iso: string|null,
    shutterSpeed: string|null,
    gpsLat: number|null,
    gpsLon: number|null,
    gpsAlt: number|null,
}

export type PhotoGroup = {
    id: number,
    description: string,
    hasMap: boolean,
    ordering: number,
    photos: Photo[],
}

export enum AlbumType {
    Album = "album",
    Dynamic = "dynamic",
    Tag = "tag",
}

export type Album = {
    id: number,
    title: string,
    description: string,
    slug: string,
    type: AlbumType,
    isPrivate: boolean,
    hasMap: boolean,
    coverPhoto: Photo|null,
    coverHash?: string,
    coverUniq?: string,
    coverAspect?: number,
    coverBlurHash?: string,
    highestIndex: number,
    photoGroups: PhotoGroup[],
}

export type PerusalData = {
    currentIdx: number,
    currentPhoto: Photo|null,
    currentGroup: PhotoGroup|null,
    photoSet: Photo[],
    nodes: (Photo|PhotoGroup)[],
}
